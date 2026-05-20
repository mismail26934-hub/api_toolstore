<?php

/**
 * Baca file CSV atau XLSX (sheet pertama) tanpa dependency eksternal.
 */
class SpreadsheetReader
{
    /**
     * @param string|null $extension Ekstensi asli file (mis. dari $_FILES['file']['name']),
     *                               karena tmp_name upload PHP biasanya tanpa ekstensi.
     */
    public static function read(
        string $filePath,
        ?string $extension = null,
    ): array {
        $ext = self::resolveExtension($filePath, $extension);
        if ($ext === "csv") {
            return self::readCsv($filePath);
        }
        if ($ext === "xlsx") {
            return self::readXlsx($filePath);
        }
        throw new RuntimeException(
            "Format tidak didukung. Gunakan .csv atau .xlsx",
        );
    }

    private static function resolveExtension(
        string $filePath,
        ?string $extension,
    ): string {
        if ($extension !== null && $extension !== "") {
            return strtolower(ltrim($extension, "."));
        }

        $fromPath = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($fromPath !== "") {
            return $fromPath;
        }

        $handle = fopen($filePath, "rb");
        if ($handle !== false) {
            $header = fread($handle, 4);
            fclose($handle);
            if ($header === "PK\x03\x04") {
                return "xlsx";
            }
        }

        return "csv";
    }

    private static function readCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            throw new RuntimeException("Gagal membuka file CSV");
        }
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map([self::class, "normalizeCell"], $row);
        }
        fclose($handle);
        return $rows;
    }

    private static function readXlsx(string $filePath): array
    {
        if (class_exists("ZipArchive")) {
            return self::readXlsxWithZipArchive($filePath);
        }

        require_once __DIR__ . "/ZipFallback.php";
        $entries = ZipFallback::readAllEntries($filePath);
        if ($entries === []) {
            throw new RuntimeException(self::xlsxUnavailableMessage());
        }

        $sharedStrings = self::parseSharedStringsXml(
            $entries["xl/sharedStrings.xml"] ?? null,
        );
        $sheetXml = $entries["xl/worksheets/sheet1.xml"] ?? false;
        if ($sheetXml === false) {
            $sheetXml = self::findFirstSheetInEntries($entries);
        }

        if ($sheetXml === false) {
            throw new RuntimeException("Sheet tidak ditemukan di file Excel");
        }

        return self::parseSheetXml($sheetXml, $sharedStrings);
    }

    private static function readXlsxWithZipArchive(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("Gagal membuka file Excel");
        }

        $sharedStringsXml = $zip->getFromName("xl/sharedStrings.xml");
        $sharedStrings = self::parseSharedStringsXml(
            $sharedStringsXml !== false ? $sharedStringsXml : null,
        );
        $sheetXml = $zip->getFromName("xl/worksheets/sheet1.xml");
        if ($sheetXml === false) {
            $sheetXml = self::findFirstSheet($zip);
        }
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException("Sheet tidak ditemukan di file Excel");
        }

        return self::parseSheetXml($sheetXml, $sharedStrings);
    }

    private static function xlsxUnavailableMessage(): string
    {
        return "Gagal membaca file .xlsx. Aktifkan extension zip di php.ini "
            . "(extension=zip), atau simpan file sebagai .csv lalu upload ulang.";
    }

    /** @param array<string, string> $entries */
    private static function findFirstSheetInEntries(array $entries)
    {
        foreach ($entries as $name => $content) {
            if (preg_match("#^xl/worksheets/sheet\d+\.xml$#", $name)) {
                return $content;
            }
        }
        return false;
    }

    /** @return string|false */
    private static function findFirstSheet(ZipArchive $zip)
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match("#^xl/worksheets/sheet\d+\.xml$#", $name ?? "")) {
                return $zip->getFromName($name);
            }
        }
        return false;
    }

    private static function parseSharedStringsXml(?string $xml): array
    {
        if ($xml === null || $xml === "") {
            return [];
        }

        $doc = simplexml_load_string($xml);
        if ($doc === false) {
            return [];
        }

        $strings = [];
        foreach ($doc->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }
            $text = "";
            foreach ($si->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }
        return $strings;
    }

    private static function parseSheetXml(
        string $sheetXml,
        array $sharedStrings,
    ): array {
        $doc = simplexml_load_string($sheetXml);
        if ($doc === false || !isset($doc->sheetData)) {
            return [];
        }

        $rows = [];
        foreach ($doc->sheetData->row as $row) {
            $rowIndex = isset($row["r"]) ? (int) $row["r"] - 1 : count($rows);
            if (!isset($rows[$rowIndex])) {
                $rows[$rowIndex] = [];
            }

            foreach ($row->c as $cell) {
                $ref = (string) $cell["r"];
                $colIndex = self::columnIndexFromRef($ref);
                $type = (string) ($cell["t"] ?? "");
                $value = isset($cell->v) ? (string) $cell->v : "";

                if (
                    $type === "s" &&
                    $value !== "" &&
                    isset($sharedStrings[(int) $value])
                ) {
                    $value = $sharedStrings[(int) $value];
                }

                $rows[$rowIndex][$colIndex] = self::normalizeCell($value);
            }
        }

        ksort($rows);
        $normalized = [];
        foreach ($rows as $rowCells) {
            if ($rowCells === []) {
                $normalized[] = [];
                continue;
            }
            ksort($rowCells);
            $maxCol = max(array_keys($rowCells));
            $line = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $line[] = $rowCells[$c] ?? "";
            }
            $normalized[] = $line;
        }

        return $normalized;
    }

    private static function columnIndexFromRef(string $ref): int
    {
        preg_match("/^[A-Z]+/", strtoupper($ref), $m);
        $letters = $m[0] ?? "A";
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }
        return $index - 1;
    }

    private static function normalizeCell(mixed $value): string
    {
        if ($value === null) {
            return "";
        }
        return trim((string) $value);
    }
}

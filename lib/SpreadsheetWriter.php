<?php

/**
 * Tulis file XLSX (sheet pertama) tanpa dependency eksternal.
 */
class SpreadsheetWriter
{
    /**
     * @param list<string> $headers
     * @param iterable<int, array<string, mixed>> $rows
     */
    public static function outputXlsxDownload(
        string $downloadFilename,
        array $headers,
        iterable $rows,
    ): void {
        if (!class_exists("ZipArchive")) {
            throw new RuntimeException(
                "Extension zip tidak tersedia. Aktifkan extension=zip di php.ini.",
            );
        }

        $tmp = tempnam(sys_get_temp_dir(), "xlsx_export_");
        if ($tmp === false) {
            throw new RuntimeException("Gagal membuat file sementara untuk export.");
        }

        try {
            self::writeXlsxFile($tmp, $headers, $rows);

            if (!headers_sent()) {
                header(
                    "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                );
                header(
                    'Content-Disposition: attachment; filename="' .
                        self::safeDownloadFilename($downloadFilename) .
                        '"',
                );
                header("Content-Length: " . (string) filesize($tmp));
                header("Cache-Control: max-age=0, no-cache, must-revalidate");
                header("Pragma: public");
            }

            readfile($tmp);
        } finally {
            if (is_file($tmp)) {
                unlink($tmp);
            }
        }
    }

    /**
     * @param list<string> $headers
     * @param iterable<int, array<string, mixed>> $rows
     */
    public static function writeXlsxFile(
        string $filePath,
        array $headers,
        iterable $rows,
    ): void {
        if (!class_exists("ZipArchive")) {
            throw new RuntimeException(
                "Extension zip tidak tersedia. Aktifkan extension=zip di php.ini.",
            );
        }

        $sheetXml = self::buildSheetXml($headers, $rows);
        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Gagal membuat file Excel.");
        }

        $zip->addFromString("[Content_Types].xml", self::contentTypesXml());
        $zip->addFromString("_rels/.rels", self::rootRelsXml());
        $zip->addFromString("xl/workbook.xml", self::workbookXml());
        $zip->addFromString("xl/_rels/workbook.xml.rels", self::workbookRelsXml());
        $zip->addFromString("xl/styles.xml", self::stylesXml());
        $zip->addFromString("xl/worksheets/sheet1.xml", $sheetXml);
        $zip->close();
    }

    /**
     * @param list<string> $headers
     * @param iterable<int, array<string, mixed>> $rows
     */
    private static function buildSheetXml(array $headers, iterable $rows): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">',
            "<sheetData>",
        ];

        $lines[] = self::buildRowXml(1, $headers);

        $rowNum = 2;
        foreach ($rows as $row) {
            $cells = [];
            foreach ($headers as $header) {
                $cells[] = self::stringifyCell($row[$header] ?? "");
            }
            $lines[] = self::buildRowXml($rowNum, $cells);
            $rowNum++;
        }

        $lines[] = "</sheetData>";
        $lines[] = "</worksheet>";

        return implode("", $lines);
    }

    /**
     * @param list<string> $values
     */
    private static function buildRowXml(int $rowNum, array $values): string
    {
        $xml = '<row r="' . $rowNum . '">';
        foreach ($values as $colIndex => $value) {
            $ref = self::columnLetter($colIndex) . (string) $rowNum;
            $xml .=
                '<c r="' .
                $ref .
                '" t="inlineStr"><is><t>' .
                self::escapeXml($value) .
                "</t></is></c>";
        }
        $xml .= "</row>";
        return $xml;
    }

    private static function stringifyCell(mixed $value): string
    {
        if ($value === null) {
            return "";
        }
        if (is_bool($value)) {
            return $value ? "1" : "0";
        }
        return trim((string) $value);
    }

    private static function columnLetter(int $index): string
    {
        $index += 1;
        $letters = "";
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = intdiv($index, 26);
        }
        return $letters;
    }

    private static function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, "UTF-8");
    }

    private static function safeDownloadFilename(string $filename): string
    {
        $filename = basename(str_replace(["\0", "\\", "/"], "", $filename));
        if ($filename === "") {
            return "export.xlsx";
        }
        if (!str_ends_with(strtolower($filename), ".xlsx")) {
            $filename .= ".xlsx";
        }
        return $filename;
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . "</Types>";
    }

    private static function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . "</Relationships>";
    }

    private static function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Form Detail" sheetId="1" r:id="rId1"/></sheets>'
            . "</workbook>";
    }

    private static function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . "</Relationships>";
    }

    private static function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . "</styleSheet>";
    }
}

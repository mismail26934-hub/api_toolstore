<?php

/**
 * Baca isi file di dalam arsip ZIP tanpa class ZipArchive (butuh zlib untuk .xlsx).
 */
class ZipFallback
{
    /** @return array<string, string> */
    public static function readAllEntries(string $zipPath): array
    {
        $data = @file_get_contents($zipPath);
        if ($data === false || $data === "") {
            return [];
        }

        $eocd = strrpos($data, "PK\x05\x06");
        if ($eocd === false) {
            return [];
        }

        $cdOffset = unpack("V", substr($data, $eocd + 16, 4))[1] ?? 0;
        $cdSize = unpack("V", substr($data, $eocd + 12, 4))[1] ?? 0;
        if ($cdSize <= 0) {
            return [];
        }

        $entries = [];
        $pos = $cdOffset;
        $cdEnd = $cdOffset + $cdSize;

        while ($pos + 46 <= $cdEnd) {
            if (substr($data, $pos, 4) !== "PK\x01\x02") {
                break;
            }

            $method = unpack("v", substr($data, $pos + 10, 2))[1] ?? 0;
            $compressedSize = unpack("V", substr($data, $pos + 20, 4))[1] ?? 0;
            $fileNameLen = unpack("v", substr($data, $pos + 28, 2))[1] ?? 0;
            $extraLen = unpack("v", substr($data, $pos + 30, 2))[1] ?? 0;
            $commentLen = unpack("v", substr($data, $pos + 32, 2))[1] ?? 0;
            $localOffset = unpack("V", substr($data, $pos + 42, 4))[1] ?? 0;

            $name = substr($data, $pos + 46, $fileNameLen);
            $pos += 46 + $fileNameLen + $extraLen + $commentLen;

            if ($name === "" || $name[strlen($name) - 1] === "/") {
                continue;
            }

            $content = self::readLocalEntry(
                $data,
                $localOffset,
                $method,
                $compressedSize,
            );
            if ($content !== false) {
                $entries[str_replace("\\", "/", $name)] = $content;
            }
        }

        return $entries;
    }

    /**
     * @return string|false
     */
    private static function readLocalEntry(
        string $data,
        int $offset,
        int $method,
        int $expectedSize,
    ) {
        if ($offset + 30 > strlen($data)) {
            return false;
        }
        if (substr($data, $offset, 4) !== "PK\x03\x04") {
            return false;
        }

        $flags = unpack("v", substr($data, $offset + 6, 2))[1] ?? 0;
        $localMethod = unpack("v", substr($data, $offset + 8, 2))[1] ?? 0;
        $compressedSize = unpack("V", substr($data, $offset + 18, 4))[1] ?? 0;
        $fileNameLen = unpack("v", substr($data, $offset + 26, 2))[1] ?? 0;
        $extraLen = unpack("v", substr($data, $offset + 28, 2))[1] ?? 0;

        $dataStart = $offset + 30 + $fileNameLen + $extraLen;
        $size = $compressedSize > 0 ? $compressedSize : $expectedSize;
        if ($size <= 0 || $dataStart + $size > strlen($data)) {
            return false;
        }

        $compressed = substr($data, $dataStart, $size);
        $useMethod = $localMethod !== 0 ? $localMethod : $method;

        if (($flags & 0x08) !== 0) {
            return false;
        }

        return self::decompress($compressed, $useMethod);
    }

    /**
     * @return string|false
     */
    private static function decompress(string $compressed, int $method)
    {
        if ($method === 0) {
            return $compressed;
        }

        if ($method !== 8) {
            return false;
        }

        if (!function_exists("gzinflate") && !function_exists("inflate_init")) {
            return false;
        }

        $attempts = [
            static fn(string $d) => @gzinflate($d),
            static fn(string $d) => @gzinflate(substr($d, 2, -4)),
            static fn(string $d) => @gzuncompress($d),
        ];

        if (function_exists("inflate_init") && defined("ZLIB_ENCODING_RAW")) {
            $attempts[] = static function (string $d) {
                $ctx = @inflate_init(ZLIB_ENCODING_RAW);
                if ($ctx === false) {
                    return false;
                }
                $out = @inflate_add($ctx, $d, ZLIB_FINISH);
                return $out === false ? false : $out;
            };
        }

        foreach ($attempts as $inflate) {
            $result = $inflate($compressed);
            if ($result !== false && $result !== "") {
                return $result;
            }
        }

        return false;
    }
}

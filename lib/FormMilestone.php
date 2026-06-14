<?php

/**
 * Normalisasi nilai form_milestone agar konsisten dengan dashboard & notifikasi.
 */
class FormMilestone
{
    /** @var array<string, string> */
    private const ALIASES = [
        "PARTIAL RECEIVED TOOL STORE" => "PARTIAL RECEIVED BY TOOL STORE",
        "RECEIVED TOOL STORE" => "RECEIVED BY TOOL STORE",
    ];

    public static function normalize(?string $milestone): string
    {
        $s = strtoupper(trim((string) $milestone));
        $s = str_replace(".", "", $s);
        $s = preg_replace("/\s+/", " ", $s) ?? $s;

        return self::ALIASES[$s] ?? $s;
    }

    public static function displayLabel(string $normalized): string
    {
        if ($normalized === "APPROVED BY SERVICE DEPT HEAD") {
            return "APPROVED BY SERVICE DEPT. HEAD";
        }

        return $normalized;
    }
}

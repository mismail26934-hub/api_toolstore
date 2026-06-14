<?php

/**
 * Normalisasi nomor telepon Indonesia untuk WhatsApp gateway.
 */
class PhoneNumber
{
    public static function normalize(?string $number): ?string
    {
        $raw = trim((string) $number);
        if ($raw === "" || $raw === "-") {
            return null;
        }

        $digits = preg_replace("/\D/", "", $raw);
        if ($digits === null || $digits === "") {
            return null;
        }

        if (str_starts_with($digits, "0")) {
            $digits = "62" . substr($digits, 1);
        }

        if (strlen($digits) < 10) {
            return null;
        }

        return $digits;
    }
}

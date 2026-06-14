<?php

require_once __DIR__ . "/../conn/env_loader.php";
require_once __DIR__ . "/PhoneNumber.php";

/**
 * HTTP client Whacenter (WhatsApp).
 */
class WhacenterClient
{
    private const DEFAULT_URL = "https://app.whacenter.com/api/send";

    public static function isEnabled(): bool
    {
        $flag = env_value("WHACENTER_ENABLED");
        if ($flag === false || $flag === "") {
            return false;
        }

        return $flag === "1" || strtolower((string) $flag) === "true";
    }

    public static function normalizePhone(?string $number): ?string
    {
        return PhoneNumber::normalize($number);
    }

    /**
     * @return array{ok: bool, body: string|false, error: string}
     */
    public function sendText(string $number, string $message): array
    {
        $deviceId = env_value("WHACENTER_DEVICE_ID");
        if ($deviceId === false || $deviceId === "") {
            return [
                "ok" => false,
                "body" => false,
                "error" => "WHACENTER_DEVICE_ID not configured",
            ];
        }

        $phone = self::normalizePhone($number);
        if ($phone === null) {
            return [
                "ok" => false,
                "body" => false,
                "error" => "invalid phone number",
            ];
        }

        $url = env_value("WHACENTER_API_URL");
        if ($url === false || $url === "") {
            $url = self::DEFAULT_URL;
        }

        $payload = [
            "device_id" => $deviceId,
            "number" => $phone,
            "message" => $message,
        ];

        $ch = curl_init((string) $url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $body = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $ok = $err === "" && $httpCode >= 200 && $httpCode < 300;

        return [
            "ok" => $ok,
            "body" => $body,
            "error" => $err,
        ];
    }
}

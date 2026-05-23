<?php

/**
 * Rate limit sederhana berbasis file (login brute-force).
 */
function login_rate_limit_check(int $maxAttempts = 10, int $windowSeconds = 300): void
{
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $key = preg_replace("/[^a-zA-Z0-9._-]/", "_", $ip);
    $dir = sys_get_temp_dir() . "/api_toolstore_rate";
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $file = $dir . "/login_" . $key . ".json";
    $now = time();
    $data = ["count" => 0, "start" => $now];

    if (is_readable($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }
    }

    if (!isset($data["start"]) || $now - (int) $data["start"] > $windowSeconds) {
        $data = ["count" => 0, "start" => $now];
    }

    if ((int) $data["count"] >= $maxAttempts) {
        if (!headers_sent()) {
            http_response_code(429);
        }
        echo json_encode([
            "value" => "0",
            "message" => "Too many login attempts. Try again later.",
        ]);
        exit;
    }

    $data["count"] = (int) $data["count"] + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

function login_rate_limit_clear(): void
{
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $key = preg_replace("/[^a-zA-Z0-9._-]/", "_", $ip);
    $file = sys_get_temp_dir() . "/api_toolstore_rate/login_" . $key . ".json";
    if (is_file($file)) {
        @unlink($file);
    }
}

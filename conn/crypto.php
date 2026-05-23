<?php

function db_encryption_key_bytes(string $key): string
{
    return hash("sha256", $key, true);
}

/**
 * @return string|false
 */
function db_decrypt_pass(string $cipherB64, string $key)
{
    $raw = base64_decode($cipherB64, true);
    if ($raw === false || strlen($raw) < 17) {
        return false;
    }

    $iv = substr($raw, 0, 16);
    $cipher = substr($raw, 16);

    return openssl_decrypt(
        $cipher,
        "AES-256-CBC",
        db_encryption_key_bytes($key),
        OPENSSL_RAW_DATA,
        $iv,
    );
}

/**
 * @return string|false
 */
function db_encrypt_pass(string $plain, string $key)
{
    $iv = random_bytes(16);
    $cipher = openssl_encrypt(
        $plain,
        "AES-256-CBC",
        db_encryption_key_bytes($key),
        OPENSSL_RAW_DATA,
        $iv,
    );

    if ($cipher === false) {
        return false;
    }

    return base64_encode($iv . $cipher);
}

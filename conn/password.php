<?php

/**
 * Hash password baru untuk disimpan di database.
 */
function password_hash_for_storage(string $plain): string
{
    return password_hash($plain, PASSWORD_DEFAULT);
}

/**
 * Verifikasi login: bcrypt/argon2 atau legacy MD5 (32 hex).
 */
function password_verify_login(string $plain, string $stored): bool
{
    if ($stored === "") {
        return false;
    }

    if (password_is_legacy_md5($stored)) {
        return hash_equals(strtolower($stored), md5($plain));
    }

    return password_verify($plain, $stored);
}

function password_is_legacy_md5(string $stored): bool
{
    return (bool) preg_match("/^[a-f0-9]{32}$/i", $stored);
}

/**
 * Normalisasi password dari form/upload sebelum disimpan.
 * Jika sudah hash bcrypt atau MD5 legacy, biarkan; jika plain, hash bcrypt.
 */
function password_normalize_for_storage(string $value): string
{
    if ($value === "") {
        return "";
    }

    if (password_is_legacy_md5($value)) {
        return strtolower($value);
    }

    $prefix = substr($value, 0, 4);
    if (
        $prefix === "$2y$" ||
        $prefix === "$2a$" ||
        $prefix === "$2b$" ||
        str_starts_with($value, '$argon2')
    ) {
        return $value;
    }

    return password_hash_for_storage($value);
}

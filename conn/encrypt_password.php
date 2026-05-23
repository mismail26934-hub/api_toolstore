<?php

if (php_sapi_name() !== "cli") {
    http_response_code(403);
    die("CLI only.\n");
}

require_once __DIR__ . "/crypto.php";
require_once __DIR__ . "/env_loader.php";

load_dotenv(dirname(__DIR__) . "/.env");

$plain = $argv[1] ?? null;
$key = $argv[2] ?? (env_value("DB_ENCRYPTION_KEY") !== false ? env_value("DB_ENCRYPTION_KEY") : null);

if ($plain === null || $plain === "") {
    fwrite(STDERR, "Usage: php conn/encrypt_password.php <password> [encryption_key]\n");
    fwrite(STDERR, "  atau set DB_ENCRYPTION_KEY di .env lalu jalankan tanpa argumen key.\n");
    exit(1);
}

if ($key === null || $key === "") {
    fwrite(STDERR, "DB_ENCRYPTION_KEY belum diset. Berikan sebagai argumen kedua atau di .env\n");
    exit(1);
}

$encrypted = db_encrypt_pass($plain, $key);
if ($encrypted === false) {
    fwrite(STDERR, "Enkripsi gagal.\n");
    exit(1);
}

echo "Tambahkan ke .env (hosting):\n\n";
echo "DB_ENCRYPTION_KEY=" . $key . "\n";
echo "DB_PASS_ENCRYPTED=" . $encrypted . "\n";
echo "\nHapus atau kosongkan DB_PASS jika memakai mode terenkripsi.\n";

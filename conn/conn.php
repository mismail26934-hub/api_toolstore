<?php

require_once __DIR__ . "/crypto.php";
require_once __DIR__ . "/env_loader.php";
require_once __DIR__ . "/security_headers.php";

$envPath = dirname(__DIR__) . "/.env";
load_dotenv($envPath);

$appEnv = env_value("APP_ENV");
if ($appEnv === false || $appEnv === "") {
    db_config_fail();
}
$isLocal = $appEnv === "local";

if (!$isLocal && !is_readable($envPath)) {
    db_config_fail();
}

$host = require_env("DB_HOST");
$user = require_env("DB_USER");
$db = require_env("DB_NAME");

$encKey = env_value("DB_ENCRYPTION_KEY");
$encPass = env_value("DB_PASS_ENCRYPTED");
$hasEncrypted =
    $encKey !== false &&
    $encKey !== "" &&
    $encPass !== false &&
    $encPass !== "";

if ($hasEncrypted) {
    if (strlen($encKey) < 32) {
        db_config_fail();
    }
    $pass = db_decrypt_pass($encPass, $encKey);
    if ($pass === false || $pass === "") {
        db_config_fail();
    }
} elseif ($isLocal && env_value("DB_PASS") !== false) {
    $pass = env_value("DB_PASS");
} else {
    db_config_fail();
}

$sql = [
    "user" => $user,
    "pass" => $pass,
    "db" => $db,
    "host" => $host,
];

$user = $sql["user"];
$pass = $sql["pass"];
$db = $sql["db"];
$host = $sql["host"];

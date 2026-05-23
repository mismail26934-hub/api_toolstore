<?php

function load_dotenv(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === "" || $line[0] === "#") {
            continue;
        }

        $pos = strpos($line, "=");
        if ($pos === false) {
            continue;
        }

        $name = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        $len = strlen($value);
        if (
            $len >= 2 &&
            (($value[0] === '"' && $value[$len - 1] === '"') ||
                ($value[0] === "'" && $value[$len - 1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}

/**
 * @return string|false
 */
function env_value(string $name)
{
    if (array_key_exists($name, $_ENV)) {
        return $_ENV[$name];
    }

    $value = getenv($name);
    return $value !== false ? $value : false;
}

function db_config_fail(): void
{
    if (!headers_sent()) {
        http_response_code(500);
    }
    die("Database configuration error.");
}

/**
 * @return string
 */
function require_env(string $name): string
{
    $value = env_value($name);
    if ($value === false || $value === "") {
        db_config_fail();
    }
    return $value;
}

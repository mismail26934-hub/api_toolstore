<?php

require_once __DIR__ . "/env_loader.php";

/**
 * Validasi token API (auth_id_users atau id_users + token dari tb_users).
 * Panggil setelah Proses_sql dibuat.
 */
function api_guard($data): void
{
    if (!($data instanceof Proses_sql)) {
        api_auth_fail();
    }

    $required = api_auth_is_required();
    // Prefer auth_id_users (session actor); id_users is often the CRUD row target.
    $idUsers = trim(
        (string) ($_POST["auth_id_users"] ?? ($_POST["id_users"] ?? "")),
    );
    $token = trim((string) ($_POST["token"] ?? ($_POST["auth_token"] ?? "")));

    if (!$required) {
        if ($idUsers === "" && $token === "") {
            return;
        }
        if ($idUsers === "" || $token === "") {
            api_auth_fail();
        }
    } else {
        if ($idUsers === "" || $token === "") {
            api_auth_fail();
        }
    }

    if (!$data->verify_api_token($idUsers, $token)) {
        api_auth_fail();
    }
}

function api_auth_is_required(): bool
{
    return env_value("API_AUTH_REQUIRED") === "1";
}

function api_auth_fail(): void
{
    if (!headers_sent()) {
        http_response_code(401);
        header("Content-Type: application/json");
    }
    echo json_encode([
        "value" => "0",
        "message" => "Unauthorized",
    ]);
    exit();
}

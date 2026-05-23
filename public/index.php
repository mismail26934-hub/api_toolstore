<?php

declare(strict_types=1);

/**
 * Front controller — semua request API lewat /v1/...
 */
define("APP_ROOT", dirname(__DIR__));

$route = api_resolve_route();
$handler = api_route_handlers()[$route] ?? null;

if ($handler === null) {
    api_json_not_found();
}

$controllerFile = APP_ROOT . "/controller/" . $handler;
if (!is_file($controllerFile)) {
    api_json_not_found("Controller not found.");
}

chdir(APP_ROOT . "/controller");
require $controllerFile;

/**
 * Ambil path setelah segmen /v1 dari REQUEST_URI.
 */
function api_resolve_route(): string
{
    $uri = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
    if (!is_string($uri) || $uri === "") {
        return "";
    }

    $uri = rawurldecode($uri);
    if (!preg_match("#/v1(?:/(.*))?$#", $uri, $matches)) {
        return "";
    }

    $path = trim($matches[1] ?? "", "/");
    return strtolower($path);
}

/**
 * @return array<string, string> route => filename di controller/
 */
function api_route_handlers(): array
{
    return [
        "auth/login" => "login.php",

        "form" => "cont_form.php",
        "form/detail" => "cont_form_detail.php",
        "form/action-note" => "cont_action_note.php",

        "user" => "cont_user.php",
        "user/upload" => "cont_user_upload.php",

        "superior" => "cont_superrior.php",
        "superior/upload" => "cont_superrior_upload.php",

        "so" => "cont_so.php",
        "po" => "cont_po.php",

        "receive/tool" => "cont_rcv_tool.php",
        "receive/wh" => "cont_rcv_wh.php",
    ];
}

function api_json_not_found(string $message = "Not Found"): void
{
    if (!headers_sent()) {
        http_response_code(404);
        header("Content-Type: application/json; charset=utf-8");
    }
    echo json_encode(
        [
            "value" => "0",
            "message" => $message,
        ],
        JSON_UNESCAPED_UNICODE,
    );
    exit;
}

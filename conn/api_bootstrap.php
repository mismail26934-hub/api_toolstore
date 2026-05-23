<?php

/**
 * Bootstrap dan helper request untuk controller API.
 * Dipanggil setelah chdir ke folder controller/ (lihat public/index.php).
 */

function api_is_post_with_param(): bool
{
    return $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["param"]) &&
        (string) $_POST["param"] !== "";
}

/**
 * @return array{data: Proses_sql, connection: Dbs}
 */
function api_bootstrap_full(
    bool $withAuth = true,
    bool $withPassword = false,
): array {
    require_once __DIR__ . "/conn.php";

    if ($withPassword) {
        require_once __DIR__ . "/password.php";
    }
    if ($withAuth) {
        require_once __DIR__ . "/api_auth.php";
    }

    require_once dirname(__DIR__) . "/model/dbs.php";
    require_once dirname(__DIR__) . "/model/m_proses.php";

    $db = db_connection_config();
    $connection = new Dbs($db["host"], $db["user"], $db["pass"], $db["db"]);
    $data = new Proses_sql($connection);

    if ($withAuth) {
        api_guard($data);
    }

    return [
        "data" => $data,
        "connection" => $connection,
    ];
}

/** Koneksi DB + Proses_sql; opsional api_guard dan modul password. */
function api_bootstrap_data(
    bool $withAuth = true,
    bool $withPassword = false,
): Proses_sql {
    return api_bootstrap_full($withAuth, $withPassword)["data"];
}

/** Bootstrap login: tanpa auth, dengan rate limit. */
function api_bootstrap_login(): Proses_sql
{
    require_once __DIR__ . "/conn.php";
    require_once __DIR__ . "/password.php";
    require_once __DIR__ . "/rate_limit.php";
    require_once dirname(__DIR__) . "/model/dbs.php";
    require_once dirname(__DIR__) . "/model/m_proses.php";

    login_rate_limit_check();

    $db = db_connection_config();
    $connection = new Dbs($db["host"], $db["user"], $db["pass"], $db["db"]);

    return new Proses_sql($connection);
}

function api_post_param(): string
{
    return trim((string) ($_POST["param"] ?? ""));
}

function api_post_string(string $key, string $default = ""): string
{
    if (!isset($_POST[$key])) {
        return $default;
    }

    return trim((string) $_POST[$key]);
}

function api_post_int(string $key, int $default, int $min = PHP_INT_MIN): int
{
    if (!isset($_POST[$key])) {
        return $default;
    }

    $value = (int) $_POST[$key];
    if ($value < $min) {
        return $min;
    }

    return $value;
}

/**
 * @return array{page: int, limit: int, offset: int}
 */
function api_pagination_from_post(int $defaultLimit = 20): array
{
    $page = api_post_int("page", 1, 1);
    $limit = api_post_int("limit", $defaultLimit, 1);

    return [
        "page" => $page,
        "limit" => $limit,
        "offset" => ($page - 1) * $limit,
    ];
}

function api_search_from_post(): string
{
    if (isset($_POST["search"])) {
        return trim((string) $_POST["search"]);
    }
    if (isset($_POST["keyword"])) {
        return trim((string) $_POST["keyword"]);
    }

    return "";
}

function api_json_response(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header("Content-Type: application/json; charset=utf-8");
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
}

function api_json_list(int $total, array $data): void
{
    api_json_response([
        "total" => $total,
        "data" => $data,
    ]);
}

/**
 * Response standar mutasi (ADD/EDIT/DELETE) — format array [{ value, message }].
 *
 * @return array{value: string, message: string}
 */
function api_mutation_result(
    bool $success,
    string $param,
    string $failSuffix = "FAILED",
): array {
    return [
        "value" => $success ? "1" : "0",
        "message" => $success
            ? $param . " SUCCESS"
            : $param . " " . $failSuffix,
    ];
}

/** Output JSON list (VIEW) atau array mutasi, sesuai kontrak API lama. */
function api_emit_user_style_response(
    string $param,
    string $viewParam,
    array $result,
    ?int $totalUsers = null,
): void {
    if ($param === $viewParam) {
        echo json_encode(
            [
                "total" => $totalUsers ?? 0,
                "data" => $result,
            ],
            JSON_UNESCAPED_UNICODE,
        );
        return;
    }

    echo json_encode($result);
}

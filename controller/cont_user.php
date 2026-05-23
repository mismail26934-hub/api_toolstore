<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const USER_PARAM_ADD = "ADD DATA USER";
const USER_PARAM_EDIT = "EDIT DATA USER";
const USER_PARAM_VIEW = "VIEW DATA USER";
const USER_PARAM_DELETE = "DELETED DATA USER";

$result = [];
$data = api_bootstrap_data(true, true);

$param = api_post_param();
$pagination = api_pagination_from_post(20);
$limit = $pagination["limit"];
$offset = $pagination["offset"];

$id_users = $_POST["id_users"] ?? null;
$id_user_post = $_POST["id_users"] ?? null;
$username = $_POST["username"] ?? null;
$password_post = $_POST["password"] ?? null;
$nama_user = $_POST["nama_user"] ?? null;
$foto = $_POST["foto"] ?? null;
$id_tu = $_POST["id_tu"] ?? null;
$no_telp = $_POST["no_telp"] ?? null;
$token = $_POST["token"] ?? null;
$level = $_POST["level"] ?? null;
$status = $_POST["status"] ?? null;
$superior_id = $_POST["superior_id"] ?? null;
$search_user = api_search_from_post();

$password = null;
$row_user_cek = null;
$id_users_cek = null;
$username_cek = null;
$total_users = 0;

if (
    $param === USER_PARAM_ADD ||
    $param === USER_PARAM_EDIT ||
    $param === USER_PARAM_VIEW
) {
    $view_level_filter =
        $param === USER_PARAM_VIEW && trim((string) ($id_users ?? "")) === ""
            ? trim((string) ($level ?? ""))
            : "";

    $data_user = $data->data_user(
        $param === USER_PARAM_ADD || $param === USER_PARAM_EDIT
            ? ""
            : $id_users,
        $username,
        "",
        $nama_user,
        "",
        "",
        "",
        "",
        $view_level_filter,
        "",
        "",
        $param === USER_PARAM_VIEW ? $limit : null,
        $param === USER_PARAM_VIEW ? $offset : 0,
        $param === USER_PARAM_VIEW ? $search_user : "",
    );

    if ($param === USER_PARAM_ADD || $param === USER_PARAM_EDIT) {
        $row_user_cek = $data_user->fetch_object();
        if ($row_user_cek !== null) {
            $id_users_cek = $row_user_cek->id_users;
            $username_cek = $row_user_cek->username;
        }
        $password = cont_user_resolve_password(
            $password_post,
            $row_user_cek->password ?? null,
        );
    } elseif ($param === USER_PARAM_VIEW) {
        $user_rows = api_mysqli_fetch_all_objects($data_user);
        $total_users = api_crud_count_from_query(
            $data->count_user(
                $id_users,
                $username,
                $nama_user,
                $superior_id,
                $search_user,
                $view_level_filter,
            ),
        );

        foreach ($user_rows as $row_user) {
            $result[] = cont_user_format_row($row_user);
        }
    }
}

$response = cont_user_handle_mutation(
    $data,
    $param,
    $row_user_cek,
    $id_users,
    $id_user_post,
    $id_users_cek,
    $username,
    $username_cek,
    $password,
    $nama_user,
    $foto,
    $id_tu,
    $no_telp,
    $token,
    $level,
    $status,
    $superior_id,
);

if (
    $param === USER_PARAM_ADD ||
    $param === USER_PARAM_EDIT ||
    $param === USER_PARAM_DELETE
) {
    $result[] = $response;
}

api_crud_emit($param, USER_PARAM_VIEW, $result, $total_users);

/**
 * Format satu baris user untuk response VIEW (field sama dengan API lama).
 *
 * @return array<string, mixed>
 */
function cont_user_format_row(?object $row_user): array
{
    if ($row_user === null) {
        return [
            "id_users" => "",
            "username" => "",
            "password" => "",
            "nama_user" => "",
            "foto" => "",
            "id_tu" => "",
            "no_telp" => "",
            "token" => "",
            "level" => "",
            "status" => "",
            "superior_id" => "",
            "nama_superior" => "",
            "no_telp_superior" => "",
        ];
    }

    return [
        "id_users" => $row_user->id_users,
        "username" => $row_user->username,
        "password" => $row_user->password,
        "nama_user" => $row_user->nama_user,
        "foto" => $row_user->foto,
        "id_tu" => $row_user->id_tu,
        "no_telp" => $row_user->no_telp,
        "token" => $row_user->token,
        "level" => $row_user->level,
        "status" => $row_user->status,
        "superior_id" => $row_user->superior_id,
        "nama_superior" => $row_user->nama_superior ?? "",
        "no_telp_superior" => $row_user->no_telp_superior ?? "",
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_user_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_user_cek,
    $id_users,
    $id_user_post,
    $id_users_cek,
    $username,
    $username_cek,
    $password,
    $nama_user,
    $foto,
    $id_tu,
    $no_telp,
    $token,
    $level,
    $status,
    $superior_id,
): array {
    switch ($param) {
        case USER_PARAM_ADD:
            if ($row_user_cek !== null) {
                return [
                    "value" => "0",
                    "message" => "DATA USER AVAILABLE",
                ];
            }

            $add_user = $data->add_user(
                cont_user_str($id_users),
                cont_user_str($username),
                cont_user_str($password),
                cont_user_str($nama_user),
                cont_user_str($foto),
                cont_user_str($id_tu),
                cont_user_str($no_telp),
                cont_user_str($token),
                cont_user_str($level),
                cont_user_str($status),
                cont_user_str($superior_id),
            );

            return [
                "value" => $add_user ? "1" : "0",
                "message" => $add_user
                    ? $param . " SUCCESS"
                    : $param . "  FAILED",
            ];

        case USER_PARAM_EDIT:
            if ($id_users_cek != $id_user_post && $username == $username_cek) {
                return [
                    "value" => "0",
                    "message" => "USERNAME DUPLICATE !",
                ];
            }

            if ($id_users === null || $id_users === "") {
                return [
                    "value" => "0",
                    "message" => "ERROR $param !",
                ];
            }

            $edit_user = $data->edit_user(
                cont_user_str($id_users),
                cont_user_str($username),
                cont_user_str($password),
                cont_user_str($nama_user),
                cont_user_str($foto),
                cont_user_str($id_tu),
                cont_user_str($no_telp),
                cont_user_str($token),
                cont_user_str($level),
                cont_user_str($status),
                cont_user_str($superior_id),
            );

            return [
                "value" => $edit_user ? "1" : "0",
                "message" => $edit_user
                    ? $param . " SUCCESS"
                    : $param . " FAILED",
            ];

        case USER_PARAM_DELETE:
            if ($id_users === null || $id_users === "") {
                return [
                    "value" => "0",
                    "message" => "ERROR $param !",
                ];
            }

            $delete_user = $data->delete_user(
                cont_user_str($id_users),
                cont_user_str($username),
                cont_user_str($password),
                cont_user_str($nama_user),
                cont_user_str($foto),
                cont_user_str($id_tu),
                cont_user_str($no_telp),
                cont_user_str($token),
                cont_user_str($level),
                cont_user_str($status),
                cont_user_str($superior_id),
            );

            return [
                "value" => $delete_user ? "1" : "0",
                "message" => $delete_user
                    ? $param . " SUCCESS"
                    : $param . " FAILED",
            ];

        default:
            return [
                "value" => "2",
                "message" => $param . " DATA FAILED",
            ];
    }
}

/** Normalisasi field string untuk mysqli (null → ""). */
function cont_user_str($value): string
{
    if ($value === null) {
        return "";
    }

    return (string) $value;
}

/**
 * Password untuk ADD/EDIT — selalu string, sama seperti API lama.
 */
function cont_user_resolve_password($password_post, ?string $storedPassword): string
{
    $plain = $password_post === null ? "" : (string) $password_post;

    if ($storedPassword !== null && $plain === $storedPassword) {
        return $plain;
    }
    if ($plain !== "") {
        return password_normalize_for_storage($plain);
    }

    return "";
}

<?php
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["param"]) &&
    $_POST["param"] === "UPLOAD DATA USER"
) {
    require_once "../conn/conn.php";
    require_once "../conn/password.php";
    require_once "../conn/api_auth.php";
    require_once "../model/dbs.php";
    require_once "../lib/SpreadsheetReader.php";

    header("Content-Type: application/json; charset=utf-8");

    $response = [
        "value" => "0",
        "message" => "",
        "total" => 0,
        "success" => 0,
        "failed" => 0,
        "errors" => [],
    ];

    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
        $response["message"] =
            "File Excel/CSV tidak ditemukan atau gagal diupload";
        echo json_encode($response);
        exit();
    }

    $upload = $_FILES["file"];
    $ext = strtolower(pathinfo($upload["name"], PATHINFO_EXTENSION));
    if (!in_array($ext, ["csv", "xlsx"], true)) {
        $response["message"] = "Format file harus .csv atau .xlsx";
        echo json_encode($response);
        exit();
    }

    $tmpPath = $upload["tmp_name"];
    $maxSize = 5 * 1024 * 1024;
    if ($upload["size"] > $maxSize) {
        $response["message"] = "Ukuran file maksimal 5 MB";
        echo json_encode($response);
        exit();
    }

    try {
        $rows = SpreadsheetReader::read($tmpPath, $ext);
    } catch (Throwable $e) {
        $response["message"] = $e->getMessage();
        echo json_encode($response);
        exit();
    }

    if (count($rows) < 2) {
        $response["message"] = "File kosong atau hanya berisi header";
        echo json_encode($response);
        exit();
    }

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $data = new Proses_sql($connection);
    api_guard($data);
    $db = $connection->conn;

    $headers = array_map("normalize_header", $rows[0]);
    $columnMap = build_user_column_map($headers);

    $required = ["username", "password", "nama_user"];
    foreach ($required as $col) {
        if (!isset($columnMap[$col])) {
            $response[
                "message"
            ] = "Kolom wajib tidak ditemukan: $col. Lihat template/template_users.csv";
            echo json_encode($response);
            exit();
        }
    }

    $nextId = $data->get_next_user_id();
    $response["total"] = count($rows) - 1;

    for ($i = 1; $i < count($rows); $i++) {
        $rowNum = $i + 1;
        $cells = $rows[$i];

        if (is_row_empty($cells)) {
            $response["total"]--;
            continue;
        }

        $row = map_row_to_user($cells, $columnMap);

        if (
            $row["username"] === "" ||
            $row["password"] === "" ||
            $row["nama_user"] === ""
        ) {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "username, password, dan nama_user wajib diisi",
            ];
            continue;
        }

        if ($row["id_users"] === "") {
            $row["id_users"] = (string) $nextId;
            $nextId++;
        }

        if (!ctype_digit($row["id_users"])) {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "id_users harus angka",
            ];
            continue;
        }

        if ($data->user_id_exists($row["id_users"])) {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "id_users {$row["id_users"]} sudah ada",
            ];
            continue;
        }

        if ($data->username_exists($row["username"])) {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "username {$row["username"]} sudah ada",
            ];
            continue;
        }

        $password = password_normalize_for_storage((string) $row["password"]);

        $superiorId = $row["superior_id"] !== "" ? $row["superior_id"] : "0";

        $idUsers = $db->real_escape_string($row["id_users"]);
        $username = $db->real_escape_string($row["username"]);
        $passwordEsc = $db->real_escape_string($password);
        $namaUser = $db->real_escape_string($row["nama_user"]);
        $foto = $db->real_escape_string(
            $row["foto"] !== "" ? $row["foto"] : "-",
        );
        $idTu = $db->real_escape_string(
            $row["id_tu"] !== "" ? $row["id_tu"] : "-",
        );
        $noTelp = $db->real_escape_string(
            $row["no_telp"] !== "" ? $row["no_telp"] : "-",
        );
        $token = $db->real_escape_string(
            $row["token"] !== "" ? $row["token"] : "-",
        );
        $level = $db->real_escape_string(
            $row["level"] !== "" ? $row["level"] : "user",
        );
        $status = $db->real_escape_string(
            $row["status"] !== "" ? $row["status"] : "active",
        );
        $superiorIdEsc = $db->real_escape_string($superiorId);

        $inserted = $data->add_user(
            $idUsers,
            $username,
            $passwordEsc,
            $namaUser,
            $foto,
            $idTu,
            $noTelp,
            $token,
            $level,
            $status,
            $superiorIdEsc,
        );

        if ($inserted) {
            $response["success"]++;
        } else {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "Gagal insert ke database",
            ];
        }
    }

    if ($response["success"] > 0 && $response["failed"] === 0) {
        $response["value"] = "1";
        $response["message"] = "UPLOAD DATA USER SUCCESS";
    } elseif ($response["success"] > 0) {
        $response["value"] = "1";
        $response[
            "message"
        ] = "UPLOAD selesai sebagian: {$response["success"]} berhasil, {$response["failed"]} gagal";
    } else {
        $response["value"] = "0";
        $response["message"] = "UPLOAD DATA USER FAILED";
    }

    echo json_encode($response);
    exit();
}

function normalize_header(string $header): string
{
    return strtolower(trim(str_replace([" ", "-"], "_", $header)));
}

function build_user_column_map(array $headers): array
{
    $aliases = [
        "id_users" => ["id_users", "id_user", "id"],
        "username" => ["username", "user_name", "user"],
        "password" => ["password", "pass", "pwd"],
        "nama_user" => ["nama_user", "nama", "name", "full_name"],
        "foto" => ["foto", "photo", "picture"],
        "id_tu" => ["id_tu", "idtu"],
        "no_telp" => ["no_telp", "no_telepon", "telepon", "phone", "hp"],
        "token" => ["token"],
        "level" => ["level", "role"],
        "status" => ["status"],
        "superior_id" => ["superior_id", "superior"],
    ];

    $map = [];
    foreach ($aliases as $field => $names) {
        foreach ($headers as $index => $header) {
            if (in_array($header, $names, true)) {
                $map[$field] = $index;
                break;
            }
        }
    }
    return $map;
}

function map_row_to_user(array $cells, array $columnMap): array
{
    $fields = [
        "id_users",
        "username",
        "password",
        "nama_user",
        "foto",
        "id_tu",
        "no_telp",
        "token",
        "level",
        "status",
        "superior_id",
    ];
    $row = [];
    foreach ($fields as $field) {
        $index = $columnMap[$field] ?? null;
        $row[$field] =
            $index !== null ? trim((string) ($cells[$index] ?? "")) : "";
    }
    return $row;
}

function is_row_empty(array $cells): bool
{
    foreach ($cells as $cell) {
        if (trim((string) $cell) !== "") {
            return false;
        }
    }
    return true;
}

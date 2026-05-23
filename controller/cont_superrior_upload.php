<?php
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["param"]) &&
    $_POST["param"] === "UPLOAD DATA SUPERRIOR"
) {
    require_once __DIR__ . "/../conn/api_bootstrap.php";
    require_once __DIR__ . "/../lib/SpreadsheetReader.php";

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

    $boot = api_bootstrap_full();
    $data = $boot["data"];
    $db = $boot["connection"]->conn;

    $defaultUserId = trim((string) ($_POST["user_id_input_superior"] ?? ""));
    $defaultDate = trim((string) ($_POST["date_input_superior"] ?? ""));
    if ($defaultDate === "") {
        $defaultDate = date("Y-m-d H:i:s");
    }

    $headers = array_map("normalize_superior_header", $rows[0]);
    $columnMap = build_superior_column_map($headers);

    if (!isset($columnMap["nama_superior"])) {
        $response["message"] =
            "Kolom wajib tidak ditemukan: nama_superior. Lihat template/template_superior.csv";
        echo json_encode($response);
        exit();
    }

    $nextId = $data->get_next_superior_id();
    $response["total"] = count($rows) - 1;

    for ($i = 1; $i < count($rows); $i++) {
        $rowNum = $i + 1;
        $cells = $rows[$i];

        if (is_superior_row_empty($cells)) {
            $response["total"]--;
            continue;
        }

        $row = map_row_to_superior($cells, $columnMap);

        if ($row["nama_superior"] === "") {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "nama_superior wajib diisi",
            ];
            continue;
        }

        if ($row["superior_id"] === "") {
            $row["superior_id"] = (string) $nextId;
            $nextId++;
        }

        if (!ctype_digit($row["superior_id"])) {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "superior_id harus angka",
            ];
            continue;
        }

        if ($data->superior_id_exists($row["superior_id"])) {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "superior_id {$row["superior_id"]} sudah ada",
            ];
            continue;
        }

        if ($data->nama_superior_exists($row["nama_superior"])) {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" => "nama_superior {$row["nama_superior"]} sudah ada",
            ];
            continue;
        }

        $statusSuperior =
            $row["status_superior"] !== "" ? $row["status_superior"] : "active";
        $userIdInput =
            $row["user_id_input_superior"] !== ""
                ? $row["user_id_input_superior"]
                : $defaultUserId;
        $dateInput =
            $row["date_input_superior"] !== ""
                ? $row["date_input_superior"]
                : $defaultDate;

        if ($userIdInput === "") {
            $response["failed"]++;
            $response["errors"][] = [
                "row" => $rowNum,
                "message" =>
                    "user_id_input_superior wajib diisi (di file atau parameter POST)",
            ];
            continue;
        }

        $superiorId = $db->real_escape_string($row["superior_id"]);
        $namaSuperior = $db->real_escape_string($row["nama_superior"]);
        $statusEsc = $db->real_escape_string($statusSuperior);
        $userIdEsc = $db->real_escape_string($userIdInput);
        $dateEsc = $db->real_escape_string($dateInput);

        $inserted = $data->add_superrior(
            $superiorId,
            $namaSuperior,
            $statusEsc,
            $userIdEsc,
            $dateEsc,
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
        $response["message"] = "UPLOAD DATA SUPERRIOR SUCCESS";
    } elseif ($response["success"] > 0) {
        $response["value"] = "1";
        $response[
            "message"
        ] = "UPLOAD selesai sebagian: {$response["success"]} berhasil, {$response["failed"]} gagal";
    } else {
        $response["value"] = "0";
        $response["message"] = "UPLOAD DATA SUPERRIOR FAILED";
    }

    echo json_encode($response);
    exit();
}

function normalize_superior_header(string $header): string
{
    return strtolower(trim(str_replace([" ", "-"], "_", $header)));
}

function build_superior_column_map(array $headers): array
{
    $aliases = [
        "superior_id" => ["superior_id", "id_superior", "id"],
        "nama_superior" => [
            "nama_superior",
            "nama",
            "name",
            "superior",
            "nama_atasan",
        ],
        "status_superior" => ["status_superior", "status"],
        "user_id_input_superior" => [
            "user_id_input_superior",
            "user_id",
            "id_user_input",
        ],
        "date_input_superior" => [
            "date_input_superior",
            "date_input",
            "tanggal_input",
            "date",
        ],
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

function map_row_to_superior(array $cells, array $columnMap): array
{
    $fields = [
        "superior_id",
        "nama_superior",
        "status_superior",
        "user_id_input_superior",
        "date_input_superior",
    ];
    $row = [];
    foreach ($fields as $field) {
        $index = $columnMap[$field] ?? null;
        $row[$field] =
            $index !== null ? trim((string) ($cells[$index] ?? "")) : "";
    }
    return $row;
}

function is_superior_row_empty(array $cells): bool
{
    foreach ($cells as $cell) {
        if (trim((string) $cell) !== "") {
            return false;
        }
    }
    return true;
}

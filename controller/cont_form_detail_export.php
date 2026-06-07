<?php

if (
    $_SERVER["REQUEST_METHOD"] !== "POST" ||
    trim((string) ($_POST["param"] ?? "")) !== "EXPORT DATA FORM DETAIL"
) {
    return;
}

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../lib/SpreadsheetWriter.php";

$id_form = trim(
    (string) ($_POST["id_form"] ?? ($_POST["idForm"] ?? "")),
);
$from_date_update = trim(
    (string) ($_POST["from_date_update"] ?? ($_POST["fromDateUpdate"] ?? "")),
);
$to_date_update = trim(
    (string) ($_POST["to_date_update"] ?? ($_POST["toDateUpdate"] ?? "")),
);

$boot = api_bootstrap_full(true, false);
$data = $boot["data"];

$result = $data->export_form_detail_full(
    $id_form !== "" ? $id_form : null,
    $from_date_update !== "" ? $from_date_update : null,
    $to_date_update !== "" ? $to_date_update : null,
);
if (!($result instanceof mysqli_result)) {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(
        [
            "value" => "0",
            "message" => "Export gagal: query tidak menghasilkan data.",
        ],
        JSON_UNESCAPED_UNICODE,
    );
    exit();
}

$headers = [];
$rows = [];
while ($row = $result->fetch_assoc()) {
    if ($headers === []) {
        $headers = array_keys($row);
    }
    $rows[] = $row;
}
$result->free();

if ($headers === []) {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(
        [
            "value" => "0",
            "message" => "Tidak ada data untuk diexport.",
        ],
        JSON_UNESCAPED_UNICODE,
    );
    exit();
}

$filenameParts = ["form_detail_export"];
if ($id_form !== "") {
    $filenameParts[] = "form" . $id_form;
}
if ($from_date_update !== "") {
    $dateFrom = str_replace("-", "", $from_date_update);
    $dateTo = str_replace(
        "-",
        "",
        $to_date_update !== "" ? $to_date_update : $from_date_update,
    );
    $filenameParts[] = $dateFrom . "_" . $dateTo;
}
$filenameParts[] = date("Ymd_His");
$filename = implode("_", $filenameParts) . ".xlsx";

try {
    SpreadsheetWriter::outputXlsxDownload($filename, $headers, $rows);
} catch (Throwable $e) {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(
        [
            "value" => "0",
            "message" => $e->getMessage(),
        ],
        JSON_UNESCAPED_UNICODE,
    );
}
exit();

<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const SUPERIOR_PARAM_ADD = "ADD DATA SUPERRIOR";
const SUPERIOR_PARAM_EDIT = "EDIT DATA SUPERRIOR";
const SUPERIOR_PARAM_VIEW = "VIEW DATA SUPERRIOR";
const SUPERIOR_PARAM_DELETE = "DELETED DATA SUPERRIOR";

$result = [];
$data = api_bootstrap_data();
$param = api_post_param();

$superior_id = $_POST["superior_id"] ?? null;
$nama_superior = $_POST["nama_superior"] ?? null;
$status_superior = $_POST["status_superior"] ?? null;
$user_id_input_superior = $_POST["user_id_input_superior"] ?? null;
$date_input_superior = $_POST["date_input_superior"] ?? null;

$row_superrior_cek = null;
$superior_id_cek = null;
$nama_superior_cek = null;

if (
    $param === SUPERIOR_PARAM_ADD ||
    $param === SUPERIOR_PARAM_EDIT ||
    $param === SUPERIOR_PARAM_VIEW
) {
    $data_superrior = $data->data_superrior(
        $param === SUPERIOR_PARAM_ADD || $param === SUPERIOR_PARAM_EDIT
            ? ""
            : $superior_id,
        $nama_superior,
        $status_superior,
        "",
        "",
    );

    if ($param === SUPERIOR_PARAM_ADD || $param === SUPERIOR_PARAM_EDIT) {
        $row_superrior_cek = $data_superrior->fetch_object();
        if ($row_superrior_cek !== null) {
            $superior_id_cek = $row_superrior_cek->superior_id;
            $nama_superior_cek = $row_superrior_cek->nama_superior;
        }
    } elseif ($param === SUPERIOR_PARAM_VIEW) {
        foreach (
            api_mysqli_fetch_all_objects($data_superrior)
            as $row_superrior
        ) {
            $result[] = cont_superior_format_row($row_superrior);
        }
    }
}

$response = cont_superior_handle_mutation(
    $data,
    $param,
    $row_superrior_cek,
    $superior_id,
    $superior_id_cek,
    $nama_superior,
    $nama_superior_cek,
    $status_superior,
    $user_id_input_superior,
    $date_input_superior,
);

api_crud_push_mutation(
    $result,
    $param,
    $response,
    SUPERIOR_PARAM_ADD,
    SUPERIOR_PARAM_EDIT,
    SUPERIOR_PARAM_DELETE,
);

echo json_encode($result);

/**
 * @return array<string, mixed>
 */
function cont_superior_format_row(?object $row): array
{
    if ($row === null) {
        return [
            "superior_id" => "",
            "nama_superior" => "",
            "status_superior" => "",
            "user_id_input_superior" => "",
            "date_input_superior" => "",
        ];
    }

    return [
        "superior_id" => $row->superior_id,
        "nama_superior" => $row->nama_superior,
        "status_superior" => $row->status_superior,
        "user_id_input_superior" => $row->user_id_input_superior,
        "date_input_superior" => $row->date_input_superior,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_superior_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_superrior_cek,
    $superior_id,
    $superior_id_cek,
    $nama_superior,
    $nama_superior_cek,
    $status_superior,
    $user_id_input_superior,
    $date_input_superior,
): array {
    switch ($param) {
        case SUPERIOR_PARAM_ADD:
            if ($row_superrior_cek !== null) {
                return api_crud_fail("FORM NUMBER DUPLICATE");
            }

            return api_crud_ok(
                $param,
                (bool) $data->add_superrior(
                    $superior_id,
                    $nama_superior,
                    $status_superior,
                    $user_id_input_superior,
                    $date_input_superior,
                ),
                " FAILED",
            );

        case SUPERIOR_PARAM_EDIT:
            if (
                $superior_id != $superior_id_cek &&
                $nama_superior == $nama_superior_cek
            ) {
                return api_crud_fail("FORM NUMBER DUPLICATE !");
            }

            if ($superior_id === null || $superior_id === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->edit_superrior(
                    $superior_id,
                    $nama_superior,
                    $status_superior,
                    $user_id_input_superior,
                    $date_input_superior,
                ),
            );

        case SUPERIOR_PARAM_DELETE:
            if ($superior_id === null || $superior_id === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->delete_superrior($superior_id, "", "", "", ""),
            );

        default:
            return api_crud_unknown_param($param);
    }
}

<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const SO_PARAM_ADD = "ADD DATA SO";
const SO_PARAM_EDIT = "EDIT DATA SO";
const SO_PARAM_VIEW = "VIEW DATA SO";
const SO_PARAM_DELETE = "DELETED DATA SO";

$result = [];
$data = api_bootstrap_data();
$param = api_post_param();

$id_so = $_POST["id_so"] ?? null;
$id_form_detail = $_POST["id_form_detail"] ?? null;
$so = $_POST["so"] ?? null;
$eta = $_POST["eta"] ?? null;
$note_so = $_POST["note_so"] ?? null;
$date_update_so = $_POST["date_update_so"] ?? null;
$id_update_so = $_POST["id_update_so"] ?? null;

$row_so_cek = null;
$id_so_cek = null;
$id_form_detail_cek = null;
$so_cek = null;

if (
    $param === SO_PARAM_ADD ||
    $param === SO_PARAM_EDIT ||
    $param === SO_PARAM_VIEW
) {
    $data_so = $data->data_so(
        $param === SO_PARAM_ADD || $param === SO_PARAM_EDIT ? "" : $id_so,
        $id_form_detail,
        $so,
        "",
        "",
        "",
        "",
    );

    if ($param === SO_PARAM_ADD || $param === SO_PARAM_EDIT) {
        $row_so_cek = $data_so->fetch_object();
        if ($row_so_cek !== null) {
            $id_so_cek = $row_so_cek->id_so;
            $id_form_detail_cek = $row_so_cek->id_form_detail;
            $so_cek = $row_so_cek->so;
        }
    } elseif ($param === SO_PARAM_VIEW) {
        foreach (api_mysqli_fetch_all_objects($data_so) as $row_so) {
            $result[] = cont_so_format_row($row_so);
        }
    }
}

$response = cont_so_handle_mutation(
    $data,
    $param,
    $row_so_cek,
    $id_so,
    $id_so_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $so,
    $so_cek,
    $eta,
    $note_so,
    $date_update_so,
    $id_update_so,
);

api_crud_push_mutation(
    $result,
    $param,
    $response,
    SO_PARAM_ADD,
    SO_PARAM_EDIT,
    SO_PARAM_DELETE,
);

echo json_encode($result);

/**
 * @return array<string, mixed>
 */
function cont_so_format_row(?object $row): array
{
    if ($row === null) {
        return [
            "id_so" => "",
            "id_form_detail" => "",
            "so" => "",
            "eta" => "",
            "note_so" => "",
            "date_update_so" => "",
            "id_update_so" => "",
        ];
    }

    return [
        "id_so" => $row->id_so,
        "id_form_detail" => $row->id_form_detail,
        "so" => $row->so,
        "eta" => $row->eta,
        "note_so" => $row->note_so,
        "date_update_so" => $row->date_update_so,
        "id_update_so" => $row->id_update_so,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_so_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_so_cek,
    $id_so,
    $id_so_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $so,
    $so_cek,
    $eta,
    $note_so,
    $date_update_so,
    $id_update_so,
): array {
    switch ($param) {
        case SO_PARAM_ADD:
            if ($row_so_cek !== null) {
                return api_crud_fail("FORM NUMBER DUPLICATE");
            }

            return api_crud_ok(
                $param,
                (bool) $data->add_so(
                    $id_so,
                    $id_form_detail,
                    $so,
                    $eta,
                    $note_so,
                    $date_update_so,
                    $id_update_so,
                ),
                " FAILED",
            );

        case SO_PARAM_EDIT:
            if (
                $id_so != $id_so_cek &&
                $id_form_detail == $id_form_detail_cek
            ) {
                return api_crud_fail("FORM NUMBER DUPLICATE !");
            }

            if ($id_so === null || $id_so === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->edit_so(
                    $id_so,
                    $id_form_detail,
                    $so,
                    $eta,
                    $note_so,
                    $date_update_so,
                    $id_update_so,
                ),
            );

        case SO_PARAM_DELETE:
            if ($id_so === null || $id_so === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->delete_so($id_so, "", "", "", "", "", ""),
            );

        default:
            return api_crud_unknown_param($param);
    }
}

<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const PO_PARAM_ADD = "ADD DATA PO";
const PO_PARAM_EDIT = "EDIT DATA PO";
const PO_PARAM_VIEW = "VIEW DATA PO";
const PO_PARAM_DELETE = "DELETED DATA PO";

$result = [];
$data = api_bootstrap_data();
$param = api_post_param();

$id_po = $_POST["id_po"] ?? null;
$id_form_detail = $_POST["id_form_detail"] ?? null;
$po_no = $_POST["po_no"] ?? null;
$date_update_po = $_POST["date_update_po"] ?? null;
$user_update_po = $_POST["user_update_po"] ?? null;

$row_po_cek = null;
$id_po_cek = null;
$id_form_detail_cek = null;
$po_no_cek = null;

if (
    $param === PO_PARAM_ADD ||
    $param === PO_PARAM_EDIT ||
    $param === PO_PARAM_VIEW
) {
    $data_po = $data->data_po(
        $param === PO_PARAM_ADD || $param === PO_PARAM_EDIT ? "" : $id_po,
        $id_form_detail,
        $po_no,
        "",
        "",
    );

    if ($param === PO_PARAM_ADD || $param === PO_PARAM_EDIT) {
        $row_po_cek = $data_po->fetch_object();
        if ($row_po_cek !== null) {
            $id_po_cek = $row_po_cek->id_po;
            $id_form_detail_cek = $row_po_cek->id_form_detail;
            $po_no_cek = $row_po_cek->po_no;
        }
    } elseif ($param === PO_PARAM_VIEW) {
        foreach (api_mysqli_fetch_all_objects($data_po) as $row_po) {
            $result[] = cont_po_format_row($row_po);
        }
    }
}

$response = cont_po_handle_mutation(
    $data,
    $param,
    $row_po_cek,
    $id_po,
    $id_po_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $po_no,
    $po_no_cek,
    $date_update_po,
    $user_update_po,
);

api_crud_push_mutation(
    $result,
    $param,
    $response,
    PO_PARAM_ADD,
    PO_PARAM_EDIT,
    PO_PARAM_DELETE,
);

echo json_encode($result);

/**
 * @return array<string, mixed>
 */
function cont_po_format_row(?object $row): array
{
    if ($row === null) {
        return [
            "id_po" => "",
            "id_form_detail" => "",
            "po_no" => "",
            "date_update_po" => "",
            "user_update_po" => "",
        ];
    }

    return [
        "id_po" => $row->id_po,
        "id_form_detail" => $row->id_form_detail,
        "po_no" => $row->po_no,
        "date_update_po" => $row->date_update_po,
        "user_update_po" => $row->user_update_po,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_po_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_po_cek,
    $id_po,
    $id_po_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $po_no,
    $po_no_cek,
    $date_update_po,
    $user_update_po,
): array {
    switch ($param) {
        case PO_PARAM_ADD:
            if ($row_po_cek !== null) {
                return api_crud_fail("PO DUPLICATE");
            }

            return api_crud_ok(
                $param,
                (bool) $data->add_po(
                    $id_po,
                    $id_form_detail,
                    $po_no,
                    $date_update_po,
                    $user_update_po,
                ),
                " FAILED",
            );

        case PO_PARAM_EDIT:
            if (
                $id_po != $id_po_cek &&
                $id_form_detail == $id_form_detail_cek &&
                $po_no == $po_no_cek
            ) {
                return api_crud_fail("PO DUPLICATE !");
            }

            if ($id_po === null || $id_po === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->edit_po(
                    $id_po,
                    $id_form_detail,
                    $po_no,
                    $date_update_po,
                    $user_update_po,
                ),
            );

        case PO_PARAM_DELETE:
            if ($id_po === null || $id_po === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->delete_po($id_po, "", "", "", ""),
            );

        default:
            return api_crud_unknown_param($param);
    }
}

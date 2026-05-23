<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const RCV_WH_PARAM_ADD = "ADD DATA RCV WH";
const RCV_WH_PARAM_EDIT = "EDIT DATA RCV WH";
const RCV_WH_PARAM_VIEW = "VIEW DATA RCV WH";
const RCV_WH_PARAM_DELETE = "DELETED DATA RCV WH";

$result = [];
$data = api_bootstrap_data();
$param = api_post_param();

$id_rcv_wh = $_POST["id_rcv_wh"] ?? null;
$id_form_detail = $_POST["id_form_detail"] ?? null;
$rcv_wh_date = $_POST["rcv_wh_date"] ?? null;
$rcv_wh_id_input = $_POST["rcv_wh_id_input"] ?? null;
$rcv_wh_date_input = $_POST["rcv_wh_date_input"] ?? null;

$row_rcv_wh_cek = null;
$id_rcv_wh_cek = null;
$id_form_detail_cek = null;

if (
    $param === RCV_WH_PARAM_ADD ||
    $param === RCV_WH_PARAM_EDIT ||
    $param === RCV_WH_PARAM_VIEW
) {
    $data_rcv_wh = $data->data_rcv_wh(
        $param === RCV_WH_PARAM_ADD || $param === RCV_WH_PARAM_EDIT
            ? ""
            : $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    );

    if ($param === RCV_WH_PARAM_ADD || $param === RCV_WH_PARAM_EDIT) {
        $row_rcv_wh_cek = $data_rcv_wh->fetch_object();
        if ($row_rcv_wh_cek !== null) {
            $id_rcv_wh_cek = $row_rcv_wh_cek->id_rcv_wh;
            $id_form_detail_cek = $row_rcv_wh_cek->id_form_detail;
        }
    } elseif ($param === RCV_WH_PARAM_VIEW) {
        foreach (api_mysqli_fetch_all_objects($data_rcv_wh) as $row_rcv_wh) {
            $result[] = cont_rcv_wh_format_row($row_rcv_wh);
        }
    }
}

$response = cont_rcv_wh_handle_mutation(
    $data,
    $param,
    $row_rcv_wh_cek,
    $id_rcv_wh,
    $id_rcv_wh_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $rcv_wh_date,
    $rcv_wh_id_input,
    $rcv_wh_date_input,
);

api_crud_push_mutation(
    $result,
    $param,
    $response,
    RCV_WH_PARAM_ADD,
    RCV_WH_PARAM_EDIT,
    RCV_WH_PARAM_DELETE,
);

echo json_encode($result);

/**
 * @return array<string, mixed>
 */
function cont_rcv_wh_format_row(?object $row): array
{
    if ($row === null) {
        return [
            "id_rcv_wh" => "",
            "id_form_detail" => "",
            "rcv_wh_date" => "",
            "rcv_wh_id_input" => "",
            "rcv_wh_date_input" => "",
        ];
    }

    return [
        "id_rcv_wh" => $row->id_rcv_wh,
        "id_form_detail" => $row->id_form_detail,
        "rcv_wh_date" => $row->rcv_wh_date,
        "rcv_wh_id_input" => $row->rcv_wh_id_input,
        "rcv_wh_date_input" => $row->rcv_wh_date_input,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_rcv_wh_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_rcv_wh_cek,
    $id_rcv_wh,
    $id_rcv_wh_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $rcv_wh_date,
    $rcv_wh_id_input,
    $rcv_wh_date_input,
): array {
    switch ($param) {
        case RCV_WH_PARAM_ADD:
            if ($row_rcv_wh_cek !== null) {
                return api_crud_fail("FORM NUMBER DUPLICATE");
            }

            return api_crud_ok(
                $param,
                (bool) $data->add_rcv_wh(
                    $id_rcv_wh,
                    $id_form_detail,
                    $rcv_wh_date,
                    $rcv_wh_id_input,
                    $rcv_wh_date_input,
                ),
                " FAILED",
            );

        case RCV_WH_PARAM_EDIT:
            if (
                $id_rcv_wh != $id_rcv_wh_cek &&
                $id_form_detail == $id_form_detail_cek
            ) {
                return api_crud_fail("FORM NUMBER DUPLICATE !");
            }

            if ($id_rcv_wh === null || $id_rcv_wh === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->edit_rcv_wh(
                    $id_rcv_wh,
                    $id_form_detail,
                    $rcv_wh_date,
                    $rcv_wh_id_input,
                    $rcv_wh_date_input,
                ),
            );

        case RCV_WH_PARAM_DELETE:
            if ($id_rcv_wh === null || $id_rcv_wh === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->delete_rcv_wh($id_rcv_wh, "", "", "", ""),
            );

        default:
            return api_crud_unknown_param($param);
    }
}

<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const RCV_TOOL_PARAM_ADD = "ADD DATA RCV TOOL";
const RCV_TOOL_PARAM_EDIT = "EDIT DATA RCV TOOL";
const RCV_TOOL_PARAM_VIEW = "VIEW DATA RCV TOOL";
const RCV_TOOL_PARAM_DELETE = "DELETED DATA RCV TOOL";

$result = [];
$data = api_bootstrap_data();
$param = api_post_param();

$id_rcv_tool = $_POST["id_rcv_tool"] ?? null;
$id_form_detail = $_POST["id_form_detail"] ?? null;
$rcv_tool_date = $_POST["rcv_tool_date"] ?? null;
$rcv_tool_id_input = $_POST["rcv_tool_id_input"] ?? null;
$rcv_tool_date_input = $_POST["rcv_tool_date_input"] ?? null;

$row_rcv_tool_cek = null;
$id_rcv_tool_cek = null;
$id_form_detail_cek = null;

if (
    $param === RCV_TOOL_PARAM_ADD ||
    $param === RCV_TOOL_PARAM_EDIT ||
    $param === RCV_TOOL_PARAM_VIEW
) {
    $data_rcv_tool = $data->data_rcv_tool(
        $param === RCV_TOOL_PARAM_ADD || $param === RCV_TOOL_PARAM_EDIT
            ? ""
            : $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    );

    if ($param === RCV_TOOL_PARAM_ADD || $param === RCV_TOOL_PARAM_EDIT) {
        $row_rcv_tool_cek = $data_rcv_tool->fetch_object();
        if ($row_rcv_tool_cek !== null) {
            $id_rcv_tool_cek = $row_rcv_tool_cek->id_rcv_tool;
            $id_form_detail_cek = $row_rcv_tool_cek->id_form_detail;
        }
    } elseif ($param === RCV_TOOL_PARAM_VIEW) {
        foreach (
            api_mysqli_fetch_all_objects($data_rcv_tool)
            as $row_rcv_tool
        ) {
            $result[] = cont_rcv_tool_format_row($row_rcv_tool);
        }
    }
}

$response = cont_rcv_tool_handle_mutation(
    $data,
    $param,
    $row_rcv_tool_cek,
    $id_rcv_tool,
    $id_rcv_tool_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $rcv_tool_date,
    $rcv_tool_id_input,
    $rcv_tool_date_input,
);

api_crud_push_mutation(
    $result,
    $param,
    $response,
    RCV_TOOL_PARAM_ADD,
    RCV_TOOL_PARAM_EDIT,
    RCV_TOOL_PARAM_DELETE,
);

echo json_encode($result);

/**
 * @return array<string, mixed>
 */
function cont_rcv_tool_format_row(?object $row): array
{
    if ($row === null) {
        return [
            "id_rcv_tool" => "",
            "id_form_detail" => "",
            "rcv_tool_date" => "",
            "rcv_tool_id_input" => "",
            "rcv_tool_date_input" => "",
        ];
    }

    return [
        "id_rcv_tool" => $row->id_rcv_tool,
        "id_form_detail" => $row->id_form_detail,
        "rcv_tool_date" => $row->rcv_tool_date,
        "rcv_tool_id_input" => $row->rcv_tool_id_input,
        "rcv_tool_date_input" => $row->rcv_tool_date_input,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_rcv_tool_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_rcv_tool_cek,
    $id_rcv_tool,
    $id_rcv_tool_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $rcv_tool_date,
    $rcv_tool_id_input,
    $rcv_tool_date_input,
): array {
    switch ($param) {
        case RCV_TOOL_PARAM_ADD:
            if ($row_rcv_tool_cek !== null) {
                return api_crud_fail("FORM NUMBER DUPLICATE");
            }

            return api_crud_ok(
                $param,
                (bool) $data->add_rcv_tool(
                    $id_rcv_tool,
                    $id_form_detail,
                    $rcv_tool_date,
                    $rcv_tool_id_input,
                    $rcv_tool_date_input,
                ),
                " FAILED",
            );

        case RCV_TOOL_PARAM_EDIT:
            if (
                $id_rcv_tool != $id_rcv_tool_cek &&
                $id_form_detail == $id_form_detail_cek
            ) {
                return api_crud_fail("FORM NUMBER DUPLICATE !");
            }

            if ($id_rcv_tool === null || $id_rcv_tool === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->edit_rcv_tool(
                    $id_rcv_tool,
                    $id_form_detail,
                    $rcv_tool_date,
                    $rcv_tool_id_input,
                    $rcv_tool_date_input,
                ),
            );

        case RCV_TOOL_PARAM_DELETE:
            if ($id_rcv_tool === null || $id_rcv_tool === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->delete_rcv_tool($id_rcv_tool, "", "", "", ""),
            );

        default:
            return api_crud_unknown_param($param);
    }
}

<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const TOOL_PARAM_ADD = "ADD DATA TOOL";
const TOOL_PARAM_EDIT = "EDIT DATA TOOL";
const TOOL_PARAM_VIEW = "VIEW DATA TOOL";
const TOOL_PARAM_DELETE = "DELETED DATA TOOL";

$result = [];
$data = api_bootstrap_data();

$param = trim(api_post_param());
$id_form_detail = $_POST["id_form_detail"] ?? ($_POST["idFormDetail"] ?? "");
$id_form = trim(
    (string) ($_POST["id_form"] ??
        ($_POST["idFrom"] ?? ($_POST["idForm"] ?? ""))),
);
$form_comment = $_POST["form_comment"] ?? ($_POST["formComment"] ?? "");
$pn_group = $_POST["pn_group"] ?? ($_POST["pnGroup"] ?? "");
$pn_desc = $_POST["pn_desc"] ?? ($_POST["pnDesc"] ?? "");
$qty = $_POST["qty"] ?? "";
$explan = $_POST["explan"] ?? "";
$action_note = $_POST["action_note"] ?? ($_POST["actionNote"] ?? "");
$val_type = $_POST["val_type"] ?? ($_POST["valType"] ?? "");
$part_value = $_POST["part_value"] ?? ($_POST["partValue"] ?? "");
$form_detail_milestone =
    $_POST["form_detail_milestone"] ?? ($_POST["formDetailMilestone"] ?? "");
$form_detail_date = trim(
    (string) ($_POST["form_detail_date"] ?? ($_POST["formDetailDate"] ?? "")),
);
$form_detail_user = trim(
    (string) ($_POST["form_detail_user"] ?? ($_POST["formDetailUser"] ?? "")),
);

$param = cont_form_detail_normalize_param($param);

if ($action_note != "") {
    $action_note = strtoupper(substr(trim((string) $action_note), 0, 1));
}

$row_form_detail_cek = null;
$id_form_detail_cek = null;
$id_form_cek = null;
$pn_group_cek = null;

if (
    $param === TOOL_PARAM_ADD ||
    $param === TOOL_PARAM_EDIT ||
    $param === TOOL_PARAM_VIEW
) {
    $data_form_detail = $data->data_form_detail(
        $param === TOOL_PARAM_ADD || $param === TOOL_PARAM_EDIT
            ? ""
            : $id_form_detail,
        $id_form,
        "",
        $pn_group,
        "",
        "",
        "",
        "",
        "",
        "",
        "",
        "",
        "",
    );

    if ($param === TOOL_PARAM_ADD || $param === TOOL_PARAM_EDIT) {
        $row_form_detail_cek = $data_form_detail->fetch_object();
        if ($row_form_detail_cek !== null) {
            $id_form_detail_cek = $row_form_detail_cek->id_form_detail;
            $id_form_cek = $row_form_detail_cek->id_form;
            $pn_group_cek = $row_form_detail_cek->pn_group;
        }
    } elseif ($param === TOOL_PARAM_VIEW) {
        foreach (
            api_mysqli_fetch_all_objects($data_form_detail)
            as $row_form_detail
        ) {
            $result[] = cont_form_detail_format_row($row_form_detail);
        }
    }
}

$response = cont_form_detail_handle_mutation(
    $data,
    $param,
    $row_form_detail_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $id_form,
    $id_form_cek,
    $pn_group,
    $pn_group_cek,
    $form_comment,
    $pn_desc,
    $qty,
    $explan,
    $action_note,
    $val_type,
    $part_value,
    $form_detail_milestone,
    $form_detail_date,
    $form_detail_user,
);

api_crud_push_mutation(
    $result,
    $param,
    $response,
    TOOL_PARAM_ADD,
    TOOL_PARAM_EDIT,
    TOOL_PARAM_DELETE,
);

echo json_encode($result);

function cont_form_detail_normalize_param(string $param): string
{
    if ($param === "ADD DATA") {
        return TOOL_PARAM_ADD;
    }
    if ($param === "EDIT DATA") {
        return TOOL_PARAM_EDIT;
    }
    if ($param === "VIEW DATA") {
        return TOOL_PARAM_VIEW;
    }
    if ($param === "DELETE DATA TOOL" || $param === "DELETE DATA") {
        return TOOL_PARAM_DELETE;
    }

    return $param;
}

/**
 * @return array<string, mixed>
 */
function cont_form_detail_format_row(?object $row): array
{
    if ($row === null) {
        return [
            "id_form_detail" => "",
            "id_form" => "",
            "form_comment" => "",
            "pn_group" => "",
            "pn_desc" => "",
            "qty" => "",
            "explan" => "",
            "action_note" => "",
            "val_type" => "",
            "part_value" => "",
            "form_detail_milestone" => "",
            "form_detail_date" => "",
            "form_detail_user" => "",
        ];
    }

    return [
        "id_form_detail" => $row->id_form_detail,
        "id_form" => $row->id_form,
        "form_comment" => $row->form_comment,
        "pn_group" => $row->pn_group,
        "pn_desc" => $row->pn_desc,
        "qty" => $row->qty,
        "explan" => $row->explan,
        "action_note" => $row->action_note,
        "val_type" => $row->val_type,
        "part_value" => $row->part_value,
        "form_detail_milestone" => $row->form_detail_milestone,
        "form_detail_date" => $row->form_detail_date,
        "form_detail_user" => $row->form_detail_user,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_form_detail_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_form_detail_cek,
    $id_form_detail,
    $id_form_detail_cek,
    $id_form,
    $id_form_cek,
    $pn_group,
    $pn_group_cek,
    $form_comment,
    $pn_desc,
    $qty,
    $explan,
    $action_note,
    $val_type,
    $part_value,
    $form_detail_milestone,
    $form_detail_date,
    $form_detail_user,
): array {
    switch ($param) {
        case TOOL_PARAM_ADD:
            $response = null;

            if ($id_form === "" || $id_form === "0") {
                $response = api_crud_fail(
                    "ADD DATA TOOL FAILED: id_form wajib diisi",
                );
            } elseif (
                $form_detail_date === "" ||
                $form_detail_date === "0000-00-00 00:00:00"
            ) {
                $form_detail_date = date("Y-m-d H:i:s");
            }

            if (
                $response === null &&
                ($form_detail_user === "" || $form_detail_user === "0")
            ) {
                $response = api_crud_fail(
                    "ADD DATA TOOL FAILED: form_detail_user wajib diisi",
                );
            }

            if (
                $response === null &&
                $row_form_detail_cek !== null &&
                $id_form_detail == $id_form_detail_cek &&
                $id_form == $id_form_cek &&
                $pn_group == $pn_group_cek
            ) {
                $response = api_crud_fail("FORM NUMBER DUPLICATE !");
            } elseif ($response === null) {
                $add_form_detail = $data->add_form_detail(
                    $id_form_detail,
                    $id_form,
                    $form_comment,
                    $pn_group,
                    $pn_desc,
                    $qty,
                    $explan,
                    $action_note,
                    $val_type,
                    $part_value,
                    $form_detail_milestone,
                    $form_detail_date,
                    $form_detail_user,
                );
                $response = api_crud_ok(
                    $param,
                    (bool) $add_form_detail,
                    " FAILED",
                );
            }

            return $response ?? api_crud_unknown_param($param);

        case TOOL_PARAM_EDIT:
            if (
                $id_form_detail != $id_form_detail_cek &&
                $id_form == $id_form_cek
            ) {
                return api_crud_fail("FORM NUMBER DUPLICATE !");
            }

            if ($id_form_detail === null || $id_form_detail === "") {
                return [
                    "value" => "0",
                    "message" => "ERROR $param $id_form_detail !!",
                ];
            }

            return api_crud_ok(
                $param,
                (bool) $data->edit_form_detail(
                    $id_form_detail,
                    $id_form,
                    $form_comment,
                    $pn_group,
                    $pn_desc,
                    $qty,
                    $explan,
                    $action_note,
                    $val_type,
                    $part_value,
                    $form_detail_milestone,
                    $form_detail_date,
                    $form_detail_user,
                ),
            );

        case TOOL_PARAM_DELETE:
            if ($id_form_detail === null || $id_form_detail === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->delete_form_detail(
                    $id_form_detail,
                    $id_form,
                    $form_comment,
                    $pn_group,
                    $pn_desc,
                    $qty,
                    $explan,
                    $action_note,
                    $val_type,
                    $part_value,
                    $form_detail_milestone,
                    $form_detail_date,
                    $form_detail_user,
                ),
            );

        default:
            return api_crud_unknown_param($param);
    }
}

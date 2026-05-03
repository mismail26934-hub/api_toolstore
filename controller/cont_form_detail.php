<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && @$_POST["param"] != null) {
    require_once "../conn/conn.php";
    require_once "../model/dbs.php";

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $result = [];
    $data = new Proses_sql($connection);

    @$param = trim((string) ($_POST["param"] ?? ""));
    @$id_form_detail =
        $_POST["id_form_detail"] ?? ($_POST["idFormDetail"] ?? "");
    @$id_form =
        $_POST["id_form"] ?? ($_POST["idFrom"] ?? ($_POST["idForm"] ?? ""));
    @$form_comment = $_POST["form_comment"] ?? ($_POST["formComment"] ?? "");
    @$pn_group = $_POST["pn_group"] ?? ($_POST["pnGroup"] ?? "");
    @$pn_desc = $_POST["pn_desc"] ?? ($_POST["pnDesc"] ?? "");
    @$qty = $_POST["qty"] ?? "";
    @$explan = $_POST["explan"] ?? "";
    @$action_note = $_POST["action_note"] ?? ($_POST["actionNote"] ?? "");
    @$val_type = $_POST["val_type"] ?? ($_POST["valType"] ?? "");
    @$part_value = $_POST["part_value"] ?? ($_POST["partValue"] ?? "");
    @$form_detail_milestone =
        $_POST["form_detail_milestone"] ??
        ($_POST["formDetailMilestone"] ?? "");
    @$form_detail_date =
        $_POST["form_detail_date"] ?? ($_POST["formDetailDate"] ?? "");
    @$form_detail_user =
        $_POST["form_detail_user"] ?? ($_POST["formDetailUser"] ?? "");

    @$add_data_form_detail = "ADD DATA TOOL";
    @$edit_data_form_detail = "EDIT DATA TOOL";
    @$view_data_form_detail = "VIEW DATA TOOL";
    @$delete_data_form_detail = "DELETED DATA TOOL";

    if (@$param == "ADD DATA") {
        @$param = @$add_data_form_detail;
    } elseif (@$param == "EDIT DATA") {
        @$param = @$edit_data_form_detail;
    } elseif (@$param == "VIEW DATA") {
        @$param = @$view_data_form_detail;
    } elseif (@$param == "DELETE DATA TOOL" || @$param == "DELETE DATA") {
        @$param = @$delete_data_form_detail;
    }

    if (@$action_note != "") {
        @$action_note = strtoupper(substr(trim((string) $action_note), 0, 1));
    }

    if (
        @$param == @$add_data_form_detail ||
        @$param == @$edit_data_form_detail ||
        @$param == @$view_data_form_detail
    ) {
        @$data_form_detail = $data->data_form_detail(
            @$param == @$add_data_form_detail ||
            @$param == @$edit_data_form_detail
                ? ""
                : @$id_form_detail,
            @$id_form,
            "",
            @$pn_group,
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
        if (
            @$param == @$add_data_form_detail ||
            @$param == @$edit_data_form_detail
        ) {
            @$row_form_detail_cek = $data_form_detail->fetch_object();
            @$id_form_detail_cek = $row_form_detail_cek->id_form_detail;
            @$id_form_cek = $row_form_detail_cek->id_form;
            @$pn_group_cek = $row_form_detail_cek->pn_group;
        } elseif (@$param == @$view_data_form_detail) {
            while (@$row_form_detail = $data_form_detail->fetch_object()) {
                if (isset($row_form_detail)) {
                    @$id_form_detail = $row_form_detail->id_form_detail;
                    @$id_form = $row_form_detail->id_form;
                    @$form_comment = $row_form_detail->form_comment;
                    @$pn_group = $row_form_detail->pn_group;
                    @$pn_desc = $row_form_detail->pn_desc;
                    @$qty = $row_form_detail->qty;
                    @$explan = $row_form_detail->explan;
                    @$action_note = $row_form_detail->action_note;
                    @$val_type = $row_form_detail->val_type;
                    @$part_value = $row_form_detail->part_value;
                    @$form_detail_milestone =
                        $row_form_detail->form_detail_milestone;
                    @$form_detail_date = $row_form_detail->form_detail_date;
                    @$form_detail_user = $row_form_detail->form_detail_user;
                } else {
                    @$id_form_detail = "";
                    @$id_form = "";
                    @$form_comment = "";
                    @$pn_group = "";
                    @$pn_desc = "";
                    @$qty = "";
                    @$explan = "";
                    @$action_note = "";
                    @$val_type = "";
                    @$part_value = "";
                    @$form_detail_milestone = "";
                    @$form_detail_date = "";
                    @$form_detail_user = "";
                }
                $b["id_form_detail"] = $id_form_detail;
                $b["id_form"] = $id_form;
                $b["form_comment"] = $form_comment;
                $b["pn_group"] = $pn_group;
                $b["pn_desc"] = $pn_desc;
                $b["qty"] = $qty;
                $b["explan"] = $explan;
                $b["action_note"] = $action_note;
                $b["val_type"] = $val_type;
                $b["part_value"] = $part_value;
                $b["form_detail_milestone"] = $form_detail_milestone;
                $b["form_detail_date"] = $form_detail_date;
                $b["form_detail_user"] = $form_detail_user;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_form_detail:
            if (isset($row_form_detail_cek)) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE";
            } else {
                @$add_form_detail = $data->add_form_detail(
                    @$id_form_detail,
                    @$id_form,
                    @$form_comment,
                    @$pn_group,
                    @$pn_desc,
                    @$qty,
                    @$explan,
                    @$action_note,
                    @$val_type,
                    @$part_value,
                    @$form_detail_milestone,
                    @$form_detail_date,
                    @$form_detail_user,
                );
                if ($add_form_detail) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_form_detail:
            if (
                @$id_form_detail != @$id_form_detail_cek &&
                $id_form == $id_form_cek
            ) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE !";
            } elseif (@$id_form_detail == null || @$id_form_detail == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param $id_form_detail !!";
            } else {
                @$edit_form = $data->edit_form_detail(
                    @$id_form_detail,
                    @$id_form,
                    @$form_comment,
                    @$pn_group,
                    @$pn_desc,
                    @$qty,
                    @$explan,
                    @$action_note,
                    @$val_type,
                    @$part_value,
                    @$form_detail_milestone,
                    @$form_detail_date,
                    @$form_detail_user,
                );
                if ($edit_form) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        case @$delete_data_form_detail:
            if (@$id_form_detail == null || @$id_form_detail == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                $delete_form_detail_ok = $data->delete_form_detail(
                    @$id_form_detail,
                    @$id_form,
                    @$form_comment,
                    @$pn_group,
                    @$pn_desc,
                    @$qty,
                    @$explan,
                    @$action_note,
                    @$val_type,
                    @$part_value,
                    @$form_detail_milestone,
                    @$form_detail_date,
                    @$form_detail_user,
                );
                if ($delete_form_detail_ok) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        default:
            $response["value"] = "2";
            $response["message"] = "$param DATA FAILED";
            break;
    }

    switch ($param) {
        case $add_data_form_detail:
            array_push($result, $response);
            break;
        case $edit_data_form_detail:
            array_push($result, $response);
            break;
        case $delete_data_form_detail:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
} ?> 
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && @$_POST["param"] != null) {
    require_once "../conn/conn.php";
    require_once "../model/dbs.php";

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $result = [];
    $data = new Proses_sql($connection);

    @$param = $_POST["param"];
    @$id_action_note = $_POST["id_action_note"];
    @$action_note_desc = $_POST["action_note_desc"];
    @$action_date_update = $_POST["action_date_update"];
    @$action_note_user = $_POST["action_note_user"];

    @$add_data_note = "ADD DATA NOTE";
    @$edit_data_note = "EDIT DATA NOTE";
    @$view_data_note = "VIEW DATA NOTE";
    @$delete_data_note = "DELETED DATA NOTE";

    if (
        @$param == @$add_data_note ||
        @$param == @$edit_data_note ||
        @$param == @$view_data_note
    ) {
        @$data_action_note = $data->data_action_note(
            @$param == @$add_data_note || @$param == @$edit_data_note
                ? ""
                : @$id_action_note,
            @$id_form,
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        );
        if (@$param == @$add_data_note || @$param == @$edit_data_note) {
            @$row_note_cek = $data_action_note->fetch_object();
            @$id_action_note_cek = $row_note_cek->id_action_note;
            @$id_form_cek = $row_note_cek->id_form;
            @$action_note_desc_cek = $row_note_cek->action_note_desc;
            @$action_date_update_cek = $row_note_cek->action_date_update;
            @$action_note_user_cek = $row_note_cek->action_note_user;
        } elseif (@$param == @$view_data_note) {
            while (@$row_note_cek = $data_action_note->fetch_object()) {
                if (isset($row_note_cek)) {
                    @$id_action_note = $row_note_cek->id_action_note;
                    @$id_form = $row_note_cek->id_form;
                    @$action_note_desc = $row_note_cek->action_note_desc;
                    @$action_date_update = $row_note_cek->action_date_update;
                    @$action_note_user = $row_note_cek->action_note_user;
                } else {
                    @$id_action_note = "";
                    @$id_form = "";
                    @$action_note_desc = "";
                    @$action_date_update = "";
                    @$action_note_user = "";
                }
                $b["id_action_note"] = $id_action_note;
                $b["id_form"] = $id_form;
                $b["action_note_desc"] = $action_note_desc;
                $b["action_date_update"] = $action_date_update;
                $b["action_note_user"] = $action_note_user;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_note:
            if (isset($row_note_cek)) {
                $response["value"] = "0";
                $response["message"] = "DUPLICATE NOTE";
            } else {
                @$add_action_note = $data->add_action_note(
                    @$id_action_note,
                    @$id_form,
                    @$action_note_desc,
                    @$action_date_update,
                    @$action_note_user,
                );
                if ($add_action_note) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_note:
            if (
                @$id_form != @$id_form_cek &&
                $action_note_desc == $action_note_desc_cek
            ) {
                $response["value"] = "0";
                $response["message"] = "DUPLICATE NOTE!";
            } elseif (@$id_action_note == null || @$id_action_note == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$edit_action_note = $data->edit_action_note(
                    @$id_action_note,
                    @$id_form,
                    @$action_note_desc,
                    @$action_date_update,
                    @$action_note_user,
                );
                if ($edit_action_note) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        case @$delete_data_note:
            if (@$id_action_note == null || @$id_action_note == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$delete_action_note = $data->delete_action_note(
                    @$id_action_note,
                    "",
                    "",
                    "",
                    "",
                );
            }
            if (@$delete_action_note) {
                $response["value"] = "1";
                $response["message"] = "$param SUCCESS";
            } else {
                $response["value"] = "0";
                $response["message"] = "$param FAILED";
            }
            break;
        default:
            $response["value"] = "2";
            $response["message"] = "$param DATA FAILED";
            break;
    }

    switch ($param) {
        case $add_data_note:
            array_push($result, $response);
            break;
        case $edit_data_note:
            array_push($result, $response);
            break;
        case $delete_data_note:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
} ?> 
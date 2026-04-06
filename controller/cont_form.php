<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && @$_POST["param"] != null) {
    require_once "../conn/conn.php";
    require_once "../model/dbs.php";

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $result = [];
    $data = new Proses_sql($connection);

    @$param = $_POST["param"];
    @$id_from = $_POST["id_from"];
    @$form_no = $_POST["form_no"];
    @$form_serv_name = $_POST["form_serv_name"];
    @$form_check_by = $_POST["form_check_by"];
    @$form_date_serv_name = $_POST["form_date_serv_name"];
    @$form_serv_comment = $_POST["form_serv_comment"];
    @$form_superior_aprd = $_POST["form_superior_aprd"];
    @$form_superior_comment = $_POST["form_superior_comment"];
    @$form_sadmin_comment = $_POST["form_sadmin_comment"];
    @$form_shead_aprd = $_POST["form_shead_aprd"];
    @$form_shead_comment = $_POST["form_shead_comment"];
    @$form_date_check_by = $_POST["form_date_check_by"];
    @$from_date_update = $_POST["from_date_update"];
    @$form_user_update = $_POST["form_user_update"];
    @$add_data_form = "ADD DATA FORM";
    @$edit_data_form = "EDIT DATA FORM";
    @$view_data_form = "VIEW DATA FORM";
    @$delete_data_form = "DELETED DATA FORM";

    if (
        @$param == @$add_data_form ||
        @$param == @$edit_data_form ||
        @$param == @$view_data_form
    ) {
        @$data_form = $data->data_form(
            @$param == @$add_data_form || @$param == @$edit_data_form
                ? ""
                : @$id_from,
            @$form_no,
            @$form_serv_name,
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        );
        if (@$param == @$add_data_form || @$param == @$edit_data_form) {
            @$row_form_cek = $data_form->fetch_object();
            @$id_from_cek = $row_form_cek->id_from;
            @$form_no_cek = $row_form_cek->form_no;
            @$form_serv_name_cek = $row_form_cek->form_serv_name;
        } elseif (@$param == @$view_data_form) {
            while (@$row_form = $data_form->fetch_object()) {
                if (isset($row_form)) {
                    @$id_from = $row_form->id_from;
                    @$form_no = $row_form->form_no;
                    @$form_serv_name = $row_form->form_serv_name;
                    @$form_check_by = $row_form->form_check_by;
                    @$form_date_serv_name = $row_form->form_date_serv_name;
                    @$form_serv_comment = $row_form->form_serv_comment;
                    @$form_superior_aprd = $row_form->form_superior_aprd;
                    @$form_superior_comment = $row_form->form_superior_comment;
                    @$form_sadmin_comment = $row_form->form_sadmin_comment;
                    @$form_shead_aprd = $row_form->form_shead_aprd;
                    @$form_shead_comment = $row_form->form_shead_comment;
                    @$form_date_check_by = $row_form->form_date_check_by;
                    @$from_date_update = $row_form->from_date_update;
                    @$form_user_update = $row_form->form_user_update;
                } else {
                    @$id_from = "";
                    @$form_no = "";
                    @$form_serv_name = "";
                    @$form_check_by = "";
                    @$form_date_serv_name = "";
                    @$form_serv_comment = "";
                    @$form_superior_aprd = "";
                    @$form_superior_comment = "";
                    @$form_sadmin_comment = "";
                    @$form_shead_aprd = "";
                    @$form_shead_comment = "";
                    @$form_date_check_by = "";
                    @$from_date_update = "";
                    @$form_user_update = "";
                }
                $b["id_from"] = $id_from;
                $b["form_no"] = $form_no;
                $b["form_serv_name"] = $form_serv_name;
                $b["form_check_by"] = $form_check_by;
                $b["form_date_serv_name"] = $form_date_serv_name;
                $b["form_serv_comment"] = $form_serv_comment;
                $b["form_superior_aprd"] = $form_superior_aprd;
                $b["form_superior_comment"] = $form_superior_comment;
                $b["form_sadmin_comment"] = $form_sadmin_comment;
                $b["form_shead_aprd"] = $form_shead_aprd;
                $b["form_shead_comment"] = $form_shead_comment;
                $b["form_date_check_by"] = $form_date_check_by;
                $b["from_date_update"] = $from_date_update;
                $b["form_user_update"] = $form_user_update;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_form:
            if (isset($row_form_cek)) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE";
            } else {
                @$add_form = $data->add_form(
                    @$id_from,
                    @$form_no,
                    @$form_serv_name,
                    @$form_check_by,
                    @$form_date_serv_name,
                    @$form_superior_aprd,
                    @$form_superior_comment,
                    @$form_sadmin_comment,
                    @$form_shead_aprd,
                    @$form_shead_comment,
                    @$form_date_check_by,
                    @$from_date_update,
                    @$form_user_update,
                );
                if ($add_form) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_form:
            if (@$id_from != @$id_from_cek && $form_no == $form_no_cek) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE !";
            } elseif (@$id_from == null || @$id_from == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$edit_form = $data->edit_form(
                    @$id_from,
                    @$form_no,
                    @$form_serv_name,
                    @$form_check_by,
                    @$form_date_serv_name,
                    @$form_superior_aprd,
                    @$form_superior_comment,
                    @$form_sadmin_comment,
                    @$form_shead_aprd,
                    @$form_shead_comment,
                    @$form_date_check_by,
                    @$from_date_update,
                    @$form_user_update,
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
        case @$delete_data_form:
            if (@$id_from == null || @$id_from == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$delete_form = $data->delete_form(@$id_from);
            }
            if (@$delete_form) {
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
        case $add_data_form:
            array_push($result, $response);
            break;
        case $edit_data_form:
            array_push($result, $response);
            break;
        case $delete_data_form:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
} ?> 
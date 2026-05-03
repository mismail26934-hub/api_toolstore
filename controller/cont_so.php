<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && @$_POST["param"] != null) {
    require_once "../conn/conn.php";
    require_once "../model/dbs.php";

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $result = [];
    $data = new Proses_sql($connection);
    // ----------------------------------------------
    //STATUS :
    // 1.REQUEST SUPERIOR APPROVAL
    // 2.CHECK BY TOOL STORE
    // 3.REVIEW SERVICE ADMIN
    // 4.WAIT APPROVAL SERVICE DEPT. HEAD
    // 5.WAIT ORDER BY COUNTER
    // 6.WAIT ORDER BY GA
    // 7.ORDER DONE
    // 8.RECEIVED WH/GA
    // 9.RECEIVED TOOL STORE
    // 10.COMPLETED
    // 11.REJECTED
    // ----------------------------------------------
    @$param = $_POST["param"];
    @$id_so = $_POST["id_so"];
    @$id_form_detail = $_POST["id_form_detail"];
    @$so = $_POST["so"];
    @$eta = $_POST["eta"];
    @$note_so = $_POST["note_so"];
    @$date_update_so = $_POST["date_update_so"];
    @$id_update_so = $_POST["id_update_so"];

    @$add_data_so = "ADD DATA SO";
    @$edit_data_so = "EDIT DATA SO";
    @$view_data_so = "VIEW DATA SO";
    @$delete_data_so = "DELETED DATA SO";

    if (
        @$param == @$add_data_so ||
        @$param == @$edit_data_so ||
        @$param == @$view_data_so
    ) {
        @$data_so = $data->data_so(
            @$param == @$add_data_so || @$param == @$edit_data_so
                ? ""
                : @$id_so,
            @$id_form_detail,
            @$so,
            "",
            "",
            "",
            "",
        );
        if (@$param == @$add_data_so || @$param == @$edit_data_so) {
            @$row_so_cek = $data_so->fetch_object();
            @$id_so_cek = $row_so_cek->id_so;
            @$id_form_detail_cek = $row_so_cek->id_form_detail;
            @$so_cek = $row_so_cek->so;
        } elseif (@$param == @$view_data_so) {
            while (@$row_so = $data_so->fetch_object()) {
                if (isset($row_so)) {
                    @$id_so = $row_so->id_so;
                    @$id_form_detail = $row_so->id_form_detail;
                    @$so = $row_so->so;
                    @$eta = $row_so->eta;
                    @$note_so = $row_so->note_so;
                    @$date_update_so = $row_so->date_update_so;
                    @$id_update_so = $row_so->id_update_so;
                } else {
                    @$id_so = "";
                    @$id_form_detail = "";
                    @$so = "";
                    @$eta = "";
                    @$note_so = "";
                    @$date_update_so = "";
                    @$id_update_so = "";
                }
                $b["id_so"] = $id_so;
                $b["id_form_detail"] = $id_form_detail;
                $b["so"] = $so;
                $b["eta"] = $eta;
                $b["note_so"] = $note_so;
                $b["date_update_so"] = $date_update_so;
                $b["id_update_so"] = $id_update_so;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_so:
            if (isset($row_so_cek)) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE";
            } else {
                @$add_so = $data->add_so(
                    @$id_so,
                    @$id_form_detail,
                    @$so,
                    @$eta,
                    @$note_so,
                    @$date_update_so,
                    @$id_update_so,
                );
                if ($add_so) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_so:
            if (
                @$id_so != @$id_so_cek &&
                $id_form_detail == $id_form_detail_cek
            ) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE !";
            } elseif (@$id_so == null || @$id_so == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$edit_so = $data->edit_so(
                    @$id_so,
                    @$id_form_detail,
                    @$so,
                    @$eta,
                    @$note_so,
                    @$date_update_so,
                    @$id_update_so,
                );
                if ($edit_so) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        case @$delete_data_so:
            if (@$id_so == null || @$id_so == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                $delete_so = $data->delete_so(@$id_so, "", "", "", "", "", "");
                if ($delete_so) {
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
        case $add_data_so:
            array_push($result, $response);
            break;
        case $edit_data_so:
            array_push($result, $response);
            break;
        case $delete_data_so:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
} ?> 
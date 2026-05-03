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
    @$id_rcv_wh = $_POST["id_rcv_wh"];
    @$id_form_detail = $_POST["id_form_detail"];
    @$rcv_wh_date = $_POST["rcv_wh_date"];
    @$rcv_wh_id_input = $_POST["rcv_wh_id_input"];
    @$rcv_wh_date_input = $_POST["rcv_wh_date_input"];

    @$add_data_rcv_wh = "ADD DATA RCV WH";
    @$edit_data_rcv_wh = "EDIT DATA RCV WH";
    @$view_data_rcv_wh = "VIEW DATA RCV WH";
    @$delete_data_rcv_wh = "DELETED DATA RCV WH";

    if (
        @$param == @$add_data_rcv_wh ||
        @$param == @$edit_data_rcv_wh ||
        @$param == @$view_data_rcv_wh
    ) {
        @$data_rcv_wh = $data->data_rcv_wh(
            @$param == @$add_data_rcv_wh || @$param == @$edit_data_rcv_wh
                ? ""
                : @$id_rcv_wh,
            @$id_form_detail,
            @$rcv_wh_date,
            @$rcv_wh_id_input,
            @$rcv_wh_date_input,
        );
        if (@$param == @$add_data_rcv_wh || @$param == @$edit_data_rcv_wh) {
            @$row_rcv_wh_cek = $data_rcv_wh->fetch_object();
            @$id_rcv_wh_cek = $row_rcv_wh_cek->id_rcv_wh;
            @$id_form_detail_cek = $row_rcv_wh_cek->id_form_detail;
            @$rcv_wh_date_cek = $row_rcv_wh_cek->rcv_wh_date;
        } elseif (@$param == @$view_data_rcv_wh) {
            while (@$row_rcv_wh = $data_rcv_wh->fetch_object()) {
                if (isset($row_rcv_wh)) {
                    @$id_rcv_wh = $row_rcv_wh->id_rcv_wh;
                    @$id_form_detail = $row_rcv_wh->id_form_detail;
                    @$rcv_wh_date = $row_rcv_wh->rcv_wh_date;
                    @$rcv_wh_id_input = $row_rcv_wh->rcv_wh_id_input;
                    @$rcv_wh_date_input = $row_rcv_wh->rcv_wh_date_input;
                } else {
                    @$id_rcv_wh = "";
                    @$id_form_detail = "";
                    @$rcv_wh_date = "";
                    @$rcv_wh_id_input = "";
                    @$rcv_wh_date_input = "";
                }
                $b["id_rcv_wh"] = $id_rcv_wh;
                $b["id_form_detail"] = $id_form_detail;
                $b["rcv_wh_date"] = $rcv_wh_date;
                $b["rcv_wh_id_input"] = $rcv_wh_id_input;
                $b["rcv_wh_date_input"] = $rcv_wh_date_input;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_rcv_wh:
            if (isset($row_rcv_wh_cek)) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE";
            } else {
                @$add_rcv_wh = $data->add_rcv_wh(
                    @$id_rcv_wh,
                    @$id_form_detail,
                    @$rcv_wh_date,
                    @$rcv_wh_id_input,
                    @$rcv_wh_date_input,
                );
                if ($add_rcv_wh) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_rcv_wh:
            if (
                @$id_rcv_wh != @$id_rcv_wh_cek &&
                $id_form_detail == $id_form_detail_cek
            ) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE !";
            } elseif (@$id_rcv_wh == null || @$id_rcv_wh == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$edit_rcv_wh = $data->edit_rcv_wh(
                    @$id_rcv_wh,
                    @$id_form_detail,
                    @$rcv_wh_date,
                    @$rcv_wh_id_input,
                    @$rcv_wh_date_input,
                );
                if ($edit_rcv_wh) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        case @$delete_data_rcv_wh:
            if (@$id_rcv_wh == null || @$id_rcv_wh == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                $delete_rcv_wh = $data->delete_rcv_wh(
                    @$id_rcv_wh,
                    "",
                    "",
                    "",
                    "",
                );
                if ($delete_rcv_wh) {
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
        case $add_data_rcv_wh:
            array_push($result, $response);
            break;
        case $edit_data_rcv_wh:
            array_push($result, $response);
            break;
        case $delete_data_rcv_wh:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
} ?> 
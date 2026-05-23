<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && @$_POST["param"] != null) {
    require_once "../conn/conn.php";
    require_once "../conn/api_auth.php";
    require_once "../model/dbs.php";

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $result = [];
    $data = new Proses_sql($connection);
    api_guard($data);
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
    @$id_po = $_POST["id_po"];
    @$id_form_detail = $_POST["id_form_detail"];
    @$po_no = $_POST["po_no"];
    @$date_update_po = $_POST["date_update_po"];
    @$user_update_po = $_POST["user_update_po"];

    @$add_data_po = "ADD DATA PO";
    @$edit_data_po = "EDIT DATA PO";
    @$view_data_po = "VIEW DATA PO";
    @$delete_data_po = "DELETED DATA PO";

    if (
        @$param == @$add_data_po ||
        @$param == @$edit_data_po ||
        @$param == @$view_data_po
    ) {
        @$data_po = $data->data_po(
            @$param == @$add_data_po || @$param == @$edit_data_po
                ? ""
                : @$id_po,
            @$id_form_detail,
            @$po_no,
            "",
            "",
        );
        if (@$param == @$add_data_po || @$param == @$edit_data_po) {
            @$row_po_cek = $data_po->fetch_object();
            @$id_po_cek = $row_po_cek->id_po;
            @$id_form_detail_cek = $row_po_cek->id_form_detail;
            @$po_no_cek = $row_po_cek->po_no;
        } elseif (@$param == @$view_data_po) {
            while (@$row_po = $data_po->fetch_object()) {
                if (isset($row_po)) {
                    @$id_po = $row_po->id_po;
                    @$id_form_detail = $row_po->id_form_detail;
                    @$po_no = $row_po->po_no;
                    @$date_update_po = $row_po->date_update_po;
                    @$user_update_po = $row_po->user_update_po;
                } else {
                    @$id_po = "";
                    @$id_form_detail = "";
                    @$po_no = "";
                    @$date_update_po = "";
                    @$user_update_po = "";
                }
                $b["id_po"] = $id_po;
                $b["id_form_detail"] = $id_form_detail;
                $b["po_no"] = $po_no;
                $b["date_update_po"] = $date_update_po;
                $b["user_update_po"] = $user_update_po;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_po:
            if (isset($row_po_cek)) {
                $response["value"] = "0";
                $response["message"] = "PO DUPLICATE";
            } else {
                @$add_po = $data->add_po(
                    @$id_po,
                    @$id_form_detail,
                    @$po_no,
                    @$date_update_po,
                    @$user_update_po,
                );
                if ($add_po) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_po:
            if (
                @$id_po != @$id_po_cek &&
                $id_form_detail == $id_form_detail_cek &&
                $po_no == $po_no_cek
            ) {
                $response["value"] = "0";
                $response["message"] = "PO DUPLICATE !";
            } elseif (@$id_po == null || @$id_po == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$edit_po = $data->edit_po(
                    @$id_po,
                    @$id_form_detail,
                    @$po_no,
                    @$date_update_po,
                    @$user_update_po,
                );
                if ($edit_po) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        case @$delete_data_po:
            if (@$id_po == null || @$id_po == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                $delete_po = $data->delete_po(@$id_po, "", "", "", "");
                if ($delete_po) {
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
        case $add_data_po:
            array_push($result, $response);
            break;
        case $edit_data_po:
            array_push($result, $response);
            break;
        case $delete_data_po:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
} ?> 
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
    @$id_rcv_tool = $_POST["id_rcv_tool"];
    @$id_form_detail = $_POST["id_form_detail"];
    @$rcv_tool_date = $_POST["rcv_tool_date"];
    @$rcv_tool_id_input = $_POST["rcv_tool_id_input"];
    @$rcv_tool_date_input = $_POST["rcv_tool_date_input"];

    @$add_data_rcv_tool = "ADD DATA RCV TOOL";
    @$edit_data_rcv_tool = "EDIT DATA RCV TOOL";
    @$view_data_rcv_tool = "VIEW DATA RCV TOOL";
    @$delete_data_rcv_tool = "DELETED DATA RCV TOOL";

    if (
        @$param == @$add_data_rcv_tool ||
        @$param == @$edit_data_rcv_tool ||
        @$param == @$view_data_rcv_tool
    ) {
        @$data_rcv_tool = $data->data_rcv_tool(
            @$param == @$add_data_rcv_tool || @$param == @$edit_data_rcv_tool
                ? ""
                : @$id_rcv_tool,
            @$id_form_detail,
            @$rcv_tool_date,
            @$rcv_tool_id_input,
            @$rcv_tool_date_input,
        );
        if (@$param == @$add_data_rcv_tool || @$param == @$edit_data_rcv_tool) {
            @$row_rcv_tool_cek = $data_rcv_tool->fetch_object();
            @$id_rcv_tool_cek = $row_rcv_tool_cek->id_rcv_tool;
            @$id_form_detail_cek = $row_rcv_tool_cek->id_form_detail;
            @$rcv_tool_date_cek = $row_rcv_tool_cek->rcv_tool_date;
        } elseif (@$param == @$view_data_rcv_tool) {
            while (@$row_rcv_tool = $data_rcv_tool->fetch_object()) {
                if (isset($row_rcv_tool)) {
                    @$id_rcv_tool = $row_rcv_tool->id_rcv_tool;
                    @$id_form_detail = $row_rcv_tool->id_form_detail;
                    @$rcv_tool_date = $row_rcv_tool->rcv_tool_date;
                    @$rcv_tool_id_input = $row_rcv_tool->rcv_tool_id_input;
                    @$rcv_tool_date_input = $row_rcv_tool->rcv_tool_date_input;
                } else {
                    @$id_rcv_tool = "";
                    @$id_form_detail = "";
                    @$rcv_tool_date = "";
                    @$rcv_tool_id_input = "";
                    @$rcv_tool_date_input = "";
                }
                $b["id_rcv_tool"] = $id_rcv_tool;
                $b["id_form_detail"] = $id_form_detail;
                $b["rcv_tool_date"] = $rcv_tool_date;
                $b["rcv_tool_id_input"] = $rcv_tool_id_input;
                $b["rcv_tool_date_input"] = $rcv_tool_date_input;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_rcv_tool:
            if (isset($row_rcv_tool_cek)) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE";
            } else {
                @$add_rcv_tool = $data->add_rcv_tool(
                    @$id_rcv_tool,
                    @$id_form_detail,
                    @$rcv_tool_date,
                    @$rcv_tool_id_input,
                    @$rcv_tool_date_input,
                );
                if ($add_rcv_tool) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_rcv_tool:
            if (
                @$id_rcv_tool != @$id_rcv_tool_cek &&
                $id_form_detail == $id_form_detail_cek
            ) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE !";
            } elseif (@$id_rcv_tool == null || @$id_rcv_tool == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$edit_rcv_tool = $data->edit_rcv_tool(
                    @$id_rcv_tool,
                    @$id_form_detail,
                    @$rcv_tool_date,
                    @$rcv_tool_id_input,
                    @$rcv_tool_date_input,
                );
                if ($edit_rcv_tool) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        case @$delete_data_rcv_tool:
            if (@$id_rcv_tool == null || @$id_rcv_tool == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                $delete_rcv_tool = $data->delete_rcv_tool(
                    @$id_rcv_tool,
                    "",
                    "",
                    "",
                    "",
                );
                if ($delete_rcv_tool) {
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
        case $add_data_rcv_tool:
            array_push($result, $response);
            break;
        case $edit_data_rcv_tool:
            array_push($result, $response);
            break;
        case $delete_data_rcv_tool:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
} ?> 
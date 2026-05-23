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
    @$superior_id = $_POST["superior_id"];
    @$nama_superior = $_POST["nama_superior"];
    @$status_superior = $_POST["status_superior"];
    @$user_id_input_superior = $_POST["user_id_input_superior"];
    @$date_input_superior = $_POST["date_input_superior"];

    @$add_data_superrior = "ADD DATA SUPERRIOR";
    @$edit_data_superrior = "EDIT DATA SUPERRIOR";
    @$view_data_superrior = "VIEW DATA SUPERRIOR";
    @$delete_data_superrior = "DELETED DATA SUPERRIOR";

    if (
        @$param == @$add_data_superrior ||
        @$param == @$edit_data_superrior ||
        @$param == @$view_data_superrior
    ) {
        @$data_superrior = $data->data_superrior(
            @$param == @$add_data_superrior || @$param == @$edit_data_superrior
                ? ""
                : @$superior_id,
            @$nama_superior,
            @$status_superior,
            "",
            "",
            "",
            "",
        );
        if (
            @$param == @$add_data_superrior ||
            @$param == @$edit_data_superrior
        ) {
            @$row_superrior_cek = $data_superrior->fetch_object();
            @$superior_id_cek = $row_superrior_cek->superior_id;
            @$nama_superior_cek = $row_superrior_cek->nama_superior;
            @$status_superior_cek = $row_superrior_cek->status_superior;
        } elseif (@$param == @$view_data_superrior) {
            while (@$row_superrior = $data_superrior->fetch_object()) {
                if (isset($row_superrior)) {
                    @$superior_id = $row_superrior->superior_id;
                    @$nama_superior = $row_superrior->nama_superior;
                    @$status_superior = $row_superrior->status_superior;
                    @$user_id_input_superior =
                        $row_superrior->user_id_input_superior;
                    @$date_input_superior = $row_superrior->date_input_superior;
                } else {
                    @$superior_id = "";
                    @$nama_superior = "";
                    @$status_superior = "";
                    @$user_id_input_superior = "";
                    @$date_input_superior = "";
                }
                $b["superior_id"] = $superior_id;
                $b["nama_superior"] = $nama_superior;
                $b["status_superior"] = $status_superior;
                $b["user_id_input_superior"] = $user_id_input_superior;
                $b["date_input_superior"] = $date_input_superior;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_superrior:
            if (isset($row_superrior_cek)) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE";
            } else {
                @$add_superrior = $data->add_superrior(
                    @$superior_id,
                    @$nama_superior,
                    @$status_superior,
                    @$user_id_input_superior,
                    @$date_input_superior,
                );
                if ($add_superrior) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_superrior:
            if (
                @$superior_id != @$superior_id_cek &&
                $nama_superior == $nama_superior_cek
            ) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE !";
            } elseif (@$superior_id == null || @$superior_id == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$edit_superrior = $data->edit_superrior(
                    @$superior_id,
                    @$nama_superior,
                    @$status_superior,
                    @$user_id_input_superior,
                    @$date_input_superior,
                );
                if ($edit_superrior) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        case @$delete_data_superrior:
            if (@$superior_id == null || @$superior_id == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                $delete_superrior = $data->delete_superrior(
                    @$superior_id,
                    "",
                    "",
                    "",
                    "",
                );
                if ($delete_superrior) {
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
        case $add_data_superrior:
            array_push($result, $response);
            break;
        case $edit_data_superrior:
            array_push($result, $response);
            break;
        case $delete_data_superrior:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
} ?> 
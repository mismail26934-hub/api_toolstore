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
    @$page = isset($_POST["page"]) ? (int) $_POST["page"] : 1;
    if (@$page < 1) {
        @$page = 1;
    }
    @$limit = isset($_POST["limit"]) ? (int) $_POST["limit"] : 20;
    @$offset = ($page - 1) * $limit;

    @$id_form = $_POST["id_form"];
    @$form_no = $_POST["form_no"];
    @$form_serv_name = $_POST["form_serv_name"];
    @$form_check_by = $_POST["form_check_by"];
    @$form_date_check_by = $_POST["form_date_check_by"];
    @$form_date_serv_name = $_POST["form_date_serv_name"];
    @$form_serv_comment = $_POST["form_serv_comment"];
    @$form_superior_aprd = $_POST["form_superior_aprd"];
    @$form_superior_comment = $_POST["form_superior_comment"];
    @$form_sadmin_comment = $_POST["form_sadmin_comment"];
    @$form_shead_aprd = $_POST["form_shead_aprd"];
    @$form_shead_comment = $_POST["form_shead_comment"];
    @$from_date_update = $_POST["from_date_update"];
    @$form_user_update = $_POST["form_user_update"];
    @$form_date_superior_aprd = $_POST["form_date_superior_aprd"];
    @$form_date_sadmin_comment = $_POST["form_date_sadmin_comment"];
    @$form_date_shead_aprd = $_POST["form_date_shead_aprd"];
    @$form_milestone = $_POST["form_milestone"];
    @$form_status_order = $_POST["form_status_order"];
    @$search_form = "";
    if (isset($_POST["search"])) {
        @$search_form = trim((string) $_POST["search"]);
    } elseif (isset($_POST["keyword"])) {
        @$search_form = trim((string) $_POST["keyword"]);
    }
    @$search_field_form = isset($_POST["search_field"])
        ? trim((string) $_POST["search_field"])
        : "all";

    @$add_data_form = "ADD DATA FORM";
    @$edit_data_form = "EDIT DATA FORM";
    @$view_data_form = "VIEW DATA FORM";
    @$delete_data_form = "DELETED DATA FORM";
    @$dashboard_count_form = "DASHBOARD COUNT FORM";

    if (@$param == @$dashboard_count_form) {
        @$count_q = $data->count_form_dashboard();
        $dashboard_counts = [
            "draft" => 0,
            "superior_approval" => 0,
            "service_admin" => 0,
            "dept_head" => 0,
            "counter_ga" => 0,
            "tool_received_wh_ga" => 0,
            "notification_total" => 0,
        ];
        if (@$count_q) {
            @$row_cnt = $count_q->fetch_object();
            if (isset($row_cnt)) {
                $dashboard_counts["draft"] = (int) $row_cnt->draft;
                $dashboard_counts["superior_approval"] =
                    (int) $row_cnt->superior_approval;
                $dashboard_counts["service_admin"] =
                    (int) $row_cnt->service_admin;
                $dashboard_counts["dept_head"] = (int) $row_cnt->dept_head;
                $dashboard_counts["counter_ga"] = (int) $row_cnt->counter_ga;
                $dashboard_counts["tool_received_wh_ga"] =
                    (int) $row_cnt->tool_received_wh_ga;
                $dashboard_counts["notification_total"] =
                    (int) $row_cnt->notification_total;
            }
        }
        echo json_encode($dashboard_counts, JSON_UNESCAPED_UNICODE);
        exit();
    }

    if (
        @$param == @$add_data_form ||
        @$param == @$edit_data_form ||
        @$param == @$view_data_form
    ) {
        @$data_form = $data->data_form(
            @$param == @$add_data_form || @$param == @$edit_data_form
                ? ""
                : @$id_form,
            @$form_no,
            @$form_serv_name,
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            @$param == @$view_data_form ? @$limit : null,
            @$param == @$view_data_form ? @$offset : 0,
            @$param == @$view_data_form ? @$search_form : null,
            @$param == @$view_data_form ? @$search_field_form : "all",
        );
        if (@$param == @$add_data_form || @$param == @$edit_data_form) {
            @$row_form_cek = $data_form->fetch_object();
            @$id_form_cek = $row_form_cek->id_form;
            @$form_no_cek = $row_form_cek->form_no;
            @$form_serv_name_cek = $row_form_cek->form_serv_name;
        } elseif (@$param == @$view_data_form) {
            @$count_q = $data->count_form(
                "",
                "",
                "",
                @$search_form,
                @$search_field_form,
            );
            @$total_forms = 0;
            if (@$count_q) {
                @$row_cnt = $count_q->fetch_object();
                if (isset($row_cnt->cnt)) {
                    @$total_forms = (int) $row_cnt->cnt;
                }
            }
            while (@$row_form = $data_form->fetch_object()) {
                if (isset($row_form)) {
                    @$id_form = $row_form->id_form;
                    @$form_no = $row_form->form_no;
                    @$form_serv_name = $row_form->form_serv_name;
                    @$form_serv_comment = $row_form->form_serv_comment;
                    @$form_date_serv_name = $row_form->form_date_serv_name;
                    @$form_check_by = $row_form->form_check_by;
                    @$form_date_check_by = $row_form->form_date_check_by;
                    @$form_superior_aprd = $row_form->form_superior_aprd;
                    @$form_superior_comment = $row_form->form_superior_comment;
                    @$form_date_superior_aprd =
                        $row_form->form_date_superior_aprd;
                    @$form_sadmin_comment = $row_form->form_sadmin_comment;
                    @$form_date_sadmin_comment =
                        $row_form->form_date_sadmin_comment;
                    @$form_shead_aprd = $row_form->form_shead_aprd;
                    @$form_date_shead_aprd = $row_form->form_date_shead_aprd;
                    @$form_shead_comment = $row_form->form_shead_comment;

                    @$from_date_update = $row_form->from_date_update;
                    @$form_user_update = $row_form->form_user_update;
                    @$form_milestone = $row_form->form_milestone;
                    @$form_status_order = $row_form->form_status_order;
                    @$superior_id = "";
                    @$data_user = $data->data_user(
                        "",
                        "",
                        "",
                        @$form_serv_name,
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                    );
                    @$row_user = $data_user->fetch_object();
                    if (isset($row_user)) {
                        @$superior_id = $row_user->superior_id;
                    }
                } else {
                    @$id_form = "";
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
                    @$form_date_superior_aprd = "";
                    @$form_date_sadmin_comment = "";
                    @$form_date_shead_aprd = "";
                    @$form_milestone = "";
                    @$form_status_order = "";
                    @$superior_id = "";
                }
                $b["id_form"] = $id_form;
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
                $b["form_date_superior_aprd"] = $form_date_superior_aprd;
                $b["form_date_sadmin_comment"] = $form_date_sadmin_comment;
                $b["form_date_shead_aprd"] = $form_date_shead_aprd;
                $b["form_milestone"] = $form_milestone;
                $b["form_status_order"] = $form_status_order;
                $b["superior_id"] = $superior_id;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_form:
            if (isset($row_form_cek)) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE !";
            } else {
                @$add_form = $data->add_form(
                    @$id_form,
                    @$form_no,
                    @$form_serv_name,
                    @$form_serv_comment,
                    @$form_date_serv_name,
                    @$form_check_by,
                    @$form_date_check_by,
                    @$form_superior_aprd,
                    @$form_superior_comment,
                    @$form_date_superior_aprd,
                    @$form_sadmin_comment,
                    @$form_date_sadmin_comment,
                    @$form_shead_aprd,
                    @$form_shead_comment,
                    @$form_milestone,
                    @$form_status_order,
                    @$form_date_shead_aprd,
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
            if (@$id_form != @$id_form_cek && $form_no == $form_no_cek) {
                $response["value"] = "0";
                $response["message"] = "FORM NUMBER DUPLICATE !!";
            } elseif (@$id_form == null || @$id_form == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !!";
            } else {
                @$edit_form = $data->edit_form(
                    @$id_form,
                    @$form_no,
                    @$form_serv_name,
                    @$form_serv_comment,
                    @$form_date_serv_name,
                    @$form_check_by,
                    @$form_date_check_by,
                    @$form_superior_aprd,
                    @$form_superior_comment,
                    @$form_date_superior_aprd,
                    @$form_sadmin_comment,
                    @$form_date_sadmin_comment,
                    @$form_shead_aprd,
                    @$form_shead_comment,
                    @$form_milestone,
                    @$form_status_order,
                    @$form_date_shead_aprd,
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
            if (@$id_form == null || @$id_form == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                $delete_form = $data->delete_form(
                    @$id_form,
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
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
                if ($delete_form) {
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
    if (@$param == @$view_data_form) {
        echo json_encode(
            [
                "total" => isset($total_forms) ? (int) $total_forms : 0,
                "data" => $result,
            ],
            JSON_UNESCAPED_UNICODE,
        );
    } else {
        echo json_encode($result);
    }
} ?> 
<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const FORM_PARAM_ADD = "ADD DATA FORM";
const FORM_PARAM_EDIT = "EDIT DATA FORM";
const FORM_PARAM_VIEW = "VIEW DATA FORM";
const FORM_PARAM_DELETE = "DELETED DATA FORM";
const FORM_PARAM_DASHBOARD = "DASHBOARD COUNT FORM";

$result = [];
$data = api_bootstrap_data();

$param = api_post_param();
$pagination = api_pagination_from_post(20);
$limit = $pagination["limit"];
$offset = $pagination["offset"];
$search_form = api_search_from_post();
$search_field_form = isset($_POST["search_field"])
    ? trim((string) $_POST["search_field"])
    : "all";

$id_form = $_POST["id_form"] ?? null;
$form_no = $_POST["form_no"] ?? null;
$form_serv_name = $_POST["form_serv_name"] ?? null;
$form_check_by = $_POST["form_check_by"] ?? null;
$form_date_check_by = $_POST["form_date_check_by"] ?? null;
$form_date_serv_name = $_POST["form_date_serv_name"] ?? null;
$form_serv_comment = $_POST["form_serv_comment"] ?? null;
$form_superior_aprd = $_POST["form_superior_aprd"] ?? null;
$form_superior_comment = $_POST["form_superior_comment"] ?? null;
$form_sadmin_comment = $_POST["form_sadmin_comment"] ?? null;
$form_shead_aprd = $_POST["form_shead_aprd"] ?? null;
$form_shead_comment = $_POST["form_shead_comment"] ?? null;
$from_date_update = $_POST["from_date_update"] ?? null;
$form_user_update = $_POST["form_user_update"] ?? null;
$form_date_superior_aprd = $_POST["form_date_superior_aprd"] ?? null;
$form_date_sadmin_comment = $_POST["form_date_sadmin_comment"] ?? null;
$form_date_shead_aprd = $_POST["form_date_shead_aprd"] ?? null;
$form_milestone = $_POST["form_milestone"] ?? null;
$form_status_order = $_POST["form_status_order"] ?? null;

if ($param === FORM_PARAM_DASHBOARD) {
    cont_form_emit_dashboard($data);
    return;
}

$row_form_cek = null;
$id_form_cek = null;
$form_no_cek = null;
$form_serv_name_cek = null;
$total_forms = 0;

$filter_id_form = cont_form_filter_value($id_form);
$filter_form_no = cont_form_filter_value($form_no);
$filter_serv_name = cont_form_filter_value($form_serv_name);

if ($param === FORM_PARAM_ADD || $param === FORM_PARAM_EDIT) {
    $data_form = $data->data_form(
        "",
        $filter_form_no,
        $filter_serv_name,
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
        null,
        0,
        null,
        "all",
    );
    $row_form_cek = $data_form->fetch_object();
    if ($row_form_cek !== null) {
        $id_form_cek = $row_form_cek->id_form;
        $form_no_cek = $row_form_cek->form_no;
        $form_serv_name_cek = $row_form_cek->form_serv_name;
    }
} elseif ($param === FORM_PARAM_VIEW) {
    $form_view = $data->form_list_view(
        $filter_id_form,
        $filter_form_no,
        $filter_serv_name,
        $limit,
        $offset,
        $search_form,
        $search_field_form,
    );
    $total_forms = (int) ($form_view["total"] ?? 0);
    $form_rows = $form_view["rows"] ?? [];

    foreach ($form_rows as $row_form) {
        $result[] = cont_form_format_row($data, $row_form);
    }

    if ($search_form !== "" && $total_forms === 0) {
        $result = [];
    }
}

$response = cont_form_handle_mutation(
    $data,
    $param,
    $row_form_cek,
    $id_form,
    $id_form_cek,
    $form_no,
    $form_no_cek,
    $form_serv_name,
    $form_serv_comment,
    $form_date_serv_name,
    $form_check_by,
    $form_date_check_by,
    $form_superior_aprd,
    $form_superior_comment,
    $form_date_superior_aprd,
    $form_sadmin_comment,
    $form_date_sadmin_comment,
    $form_shead_aprd,
    $form_shead_comment,
    $form_milestone,
    $form_status_order,
    $form_date_shead_aprd,
    $from_date_update,
    $form_user_update,
);

api_crud_push_mutation(
    $result,
    $param,
    $response,
    FORM_PARAM_ADD,
    FORM_PARAM_EDIT,
    FORM_PARAM_DELETE,
);

api_crud_emit($param, FORM_PARAM_VIEW, $result, $total_forms);

/** Normalisasi filter list: null / spasi → kosong (hindari WHERE salah). */
function cont_form_filter_value($value): string
{
    if ($value === null) {
        return "";
    }

    return trim((string) $value);
}

function cont_form_emit_dashboard(Proses_sql $data): void
{
    $dashboard_counts = [
        "draft" => 0,
        "superior_approval" => 0,
        "service_admin" => 0,
        "dept_head" => 0,
        "counter_ga" => 0,
        "tool_received_wh_ga" => 0,
        "notification_total" => 0,
    ];

    $count_q = $data->count_form_dashboard();
    if ($count_q) {
        $row_cnt = $count_q->fetch_object();
        if ($row_cnt !== null) {
            $dashboard_counts["draft"] = (int) $row_cnt->draft;
            $dashboard_counts["superior_approval"] =
                (int) $row_cnt->superior_approval;
            $dashboard_counts["service_admin"] = (int) $row_cnt->service_admin;
            $dashboard_counts["dept_head"] = (int) $row_cnt->dept_head;
            $dashboard_counts["counter_ga"] = (int) $row_cnt->counter_ga;
            $dashboard_counts["tool_received_wh_ga"] =
                (int) $row_cnt->tool_received_wh_ga;
            $dashboard_counts["notification_total"] =
                (int) $row_cnt->notification_total;
        }
    }

    echo json_encode($dashboard_counts, JSON_UNESCAPED_UNICODE);
}

/**
 * @return array<string, mixed>
 */
function cont_form_format_row(Proses_sql $data, ?object $row_form): array
{
    if ($row_form === null) {
        return [
            "id_form" => "",
            "form_no" => "",
            "form_serv_name" => "",
            "form_check_by" => "",
            "form_date_serv_name" => "",
            "form_serv_comment" => "",
            "form_superior_aprd" => "",
            "form_superior_comment" => "",
            "form_sadmin_comment" => "",
            "form_shead_aprd" => "",
            "form_shead_comment" => "",
            "form_date_check_by" => "",
            "from_date_update" => "",
            "form_user_update" => "",
            "form_date_superior_aprd" => "",
            "form_date_sadmin_comment" => "",
            "form_date_shead_aprd" => "",
            "form_milestone" => "",
            "form_status_order" => "",
            "superior_id" => "",
        ];
    }

    $superior_id = "";
    $data_user = $data->data_user(
        "",
        "",
        "",
        $row_form->form_serv_name,
        "",
        "",
        "",
        "",
        "",
        "",
        "",
    );
    if ($data_user instanceof mysqli_result) {
        $row_user = $data_user->fetch_object();
        if ($row_user !== null) {
            $superior_id = $row_user->superior_id;
        }
        $data_user->free();
    }

    return [
        "id_form" => $row_form->id_form,
        "form_no" => $row_form->form_no,
        "form_serv_name" => $row_form->form_serv_name,
        "form_check_by" => $row_form->form_check_by,
        "form_date_serv_name" => $row_form->form_date_serv_name,
        "form_serv_comment" => $row_form->form_serv_comment,
        "form_superior_aprd" => $row_form->form_superior_aprd,
        "form_superior_comment" => $row_form->form_superior_comment,
        "form_sadmin_comment" => $row_form->form_sadmin_comment,
        "form_shead_aprd" => $row_form->form_shead_aprd,
        "form_shead_comment" => $row_form->form_shead_comment,
        "form_date_check_by" => $row_form->form_date_check_by,
        "from_date_update" => $row_form->from_date_update,
        "form_user_update" => $row_form->form_user_update,
        "form_date_superior_aprd" => $row_form->form_date_superior_aprd,
        "form_date_sadmin_comment" => $row_form->form_date_sadmin_comment,
        "form_date_shead_aprd" => $row_form->form_date_shead_aprd,
        "form_milestone" => $row_form->form_milestone,
        "form_status_order" => $row_form->form_status_order,
        "superior_id" => $superior_id,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_form_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_form_cek,
    $id_form,
    $id_form_cek,
    $form_no,
    $form_no_cek,
    $form_serv_name,
    $form_serv_comment,
    $form_date_serv_name,
    $form_check_by,
    $form_date_check_by,
    $form_superior_aprd,
    $form_superior_comment,
    $form_date_superior_aprd,
    $form_sadmin_comment,
    $form_date_sadmin_comment,
    $form_shead_aprd,
    $form_shead_comment,
    $form_milestone,
    $form_status_order,
    $form_date_shead_aprd,
    $from_date_update,
    $form_user_update,
): array {
    switch ($param) {
        case FORM_PARAM_ADD:
            if ($row_form_cek !== null) {
                return api_crud_fail("FORM NUMBER DUPLICATE !");
            }

            $add_form = $data->add_form(
                $id_form,
                $form_no,
                $form_serv_name,
                $form_serv_comment,
                $form_date_serv_name,
                $form_check_by,
                $form_date_check_by,
                $form_superior_aprd,
                $form_superior_comment,
                $form_date_superior_aprd,
                $form_sadmin_comment,
                $form_date_sadmin_comment,
                $form_shead_aprd,
                $form_shead_comment,
                $form_milestone,
                $form_status_order,
                $form_date_shead_aprd,
                $from_date_update,
                $form_user_update,
            );

            return api_crud_ok($param, (bool) $add_form, " FAILED");

        case FORM_PARAM_EDIT:
            if ($id_form != $id_form_cek && $form_no == $form_no_cek) {
                return api_crud_fail("FORM NUMBER DUPLICATE !!");
            }

            if ($id_form === null || $id_form === "") {
                return api_crud_fail("ERROR $param !!");
            }

            $edit_form = $data->edit_form(
                $id_form,
                $form_no,
                $form_serv_name,
                $form_serv_comment,
                $form_date_serv_name,
                $form_check_by,
                $form_date_check_by,
                $form_superior_aprd,
                $form_superior_comment,
                $form_date_superior_aprd,
                $form_sadmin_comment,
                $form_date_sadmin_comment,
                $form_shead_aprd,
                $form_shead_comment,
                $form_milestone,
                $form_status_order,
                $form_date_shead_aprd,
                $from_date_update,
                $form_user_update,
            );

            return api_crud_ok($param, (bool) $edit_form);

        case FORM_PARAM_DELETE:
            if ($id_form === null || $id_form === "") {
                return api_crud_fail("ERROR $param !");
            }

            $delete_form = $data->delete_form(
                $id_form,
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

            return api_crud_ok($param, (bool) $delete_form);

        default:
            return api_crud_unknown_param($param);
    }
}

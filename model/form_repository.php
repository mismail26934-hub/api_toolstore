<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository form.
 */
class FormRepository extends RepositoryBase
{
    /**
     * WHERE clause for form list / count (exact id/no/serviceman or keyword search).
     */
    private function form_list_where_sql(
        $db,
        $id_form,
        $form_no,
        $form_serv_name,
        $search,
        $search_field,
    ) {
        if ((@$id_form ?? "") !== "") {
            $id_esc = $db->real_escape_string($id_form);
            return " WHERE id_form = '$id_esc' ";
        }
        if ((@$form_no ?? "") !== "") {
            $no_esc = $db->real_escape_string($form_no);
            return " WHERE form_no = '$no_esc' ";
        }
        if ((@$form_serv_name ?? "") !== "") {
            $name_esc = $db->real_escape_string($form_serv_name);
            return " WHERE form_serv_name = '$name_esc' ";
        }

        $search_trim = trim((string) ($search ?? ""));
        if ($search_trim === "") {
            return "";
        }

        $escaped = $db->real_escape_string($search_trim);
        $like = "'%$escaped%'";
        $field = strtolower(trim((string) ($search_field ?? "all")));
        $fd = $this->tb_form_detail;
        $so = $this->tb_so;
        $po = $this->tb_po;

        $detail_match = function ($predicate) use ($fd) {
            return "EXISTS (SELECT 1 FROM $fd fd WHERE fd.id_form = tb_form.id_form AND ($predicate))";
        };
        $order_match =
            "EXISTS (SELECT 1 FROM $fd fd " .
            "LEFT JOIN $so s ON s.id_form_detail = fd.id_form_detail " .
            "LEFT JOIN $po p ON p.id_form_detail = fd.id_form_detail " .
            "WHERE fd.id_form = tb_form.id_form AND (IFNULL(s.so,'') LIKE $like OR IFNULL(s.note_so,'') LIKE $like OR IFNULL(p.po_no,'') LIKE $like))";

        switch ($field) {
            case "formno":
            case "form_no":
                return " WHERE form_no LIKE $like ";
            case "serviceman":
                return " WHERE form_serv_name LIKE $like ";
            case "status":
                return " WHERE form_serv_comment LIKE $like ";
            case "idform":
            case "id_form":
            case "category":
                return " WHERE CAST(id_form AS CHAR) LIKE $like ";
            case "pngroup":
            case "pn_group":
                return " WHERE " .
                    $detail_match("fd.pn_group LIKE $like") .
                    " ";
            case "pndesc":
            case "pn_desc":
                return " WHERE " . $detail_match("fd.pn_desc LIKE $like") . " ";
            case "all":
            default:
                return " WHERE (form_no LIKE $like OR form_serv_name LIKE $like OR form_serv_comment LIKE $like " .
                    "OR CAST(id_form AS CHAR) LIKE $like OR IFNULL(form_check_by,'') LIKE $like " .
                    "OR IFNULL(form_milestone,'') LIKE $like OR " .
                    $detail_match(
                        "fd.pn_group LIKE $like OR fd.pn_desc LIKE $like",
                    ) .
                    " OR $order_match) ";
        }
    }

    public function data_form(
        $id_form,
        $form_no,
        $form_serv_name,
        $form_check_by,
        $form_date_serv_name,
        $form_serv_comment,
        $form_superior_aprd,
        $form_superior_comment,
        $form_sadmin_comment,
        $form_shead_aprd,
        $form_shead_comment,
        $form_date_check_by,
        $from_date_update,
        $form_user_update,
        $form_date_superior_aprd,
        $form_date_sadmin_comment,
        $form_date_shead_aprd,
        $form_milestone,
        $form_status_order,
        $limit,
        $offset,
        $search = null,
        $search_field = "all",
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_form;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        $sql .= $this->form_list_where_sql(
            $db,
            $id_form,
            $form_no,
            $form_serv_name,
            $search,
            $search_field,
        );
        if (
            (@$id_form ?? "") === "" &&
            (@$form_no ?? "") === "" &&
            (@$form_serv_name ?? "") === ""
        ) {
            $sql .= " ORDER BY form_no ASC";
            if ($limit !== null && (int) $limit > 0) {
                $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
            }
        }
        $query = $this->db_query($sql);
        return $query;
    }

    /**
     * Ambil baris form untuk VIEW (array object, result set dibebaskan di sini).
     *
     * @return list<object>
     */
    public function fetch_form_list_rows(
        $id_form,
        $form_no,
        $form_serv_name,
        $limit,
        $offset,
        $search = null,
        $search_field = "all",
    ): array {
        $result = $this->data_form(
            $id_form,
            $form_no,
            $form_serv_name,
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
            $limit,
            $offset,
            $search,
            $search_field,
        );

        if (!($result instanceof mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $result->fetch_object()) {
            if ($row !== null) {
                $rows[] = $row;
            }
        }
        $result->free();

        return $rows;
    }

    /**
     * Jumlah baris form yang cocok dengan filter yang sama seperti data_form(),
     * tanpa LIMIT/OFFSET (untuk pagination di cont_form.php).
     */
    public function count_form(
        $id_form,
        $form_no,
        $form_serv_name,
        $search = null,
        $search_field = "all",
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_form;
        $sql = "SELECT COUNT(*) AS cnt FROM $table ";
        $sql .= $this->form_list_where_sql(
            $db,
            $id_form,
            $form_no,
            $form_serv_name,
            $search,
            $search_field,
        );
        $query = $this->db_query($sql);
        return $query;
    }

    /**
     * Agregat COUNT per kategori milestone (dashboard monitoring).
     * Logika selaras dengan dashboard Flutter (draft + milestone aktif).
     */
    public function count_form_dashboard()
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_form;
        $norm = "UPPER(TRIM(REPLACE(IFNULL(form_milestone,''), '.', '')))";
        $mile = "UPPER(TRIM(IFNULL(form_milestone,'')))";
        $sql =
            "SELECT " .
            "SUM(CASE WHEN TRIM(IFNULL(form_milestone,'')) = '' THEN 1 ELSE 0 END) AS draft, " .
            "SUM(CASE WHEN $mile = 'CHECK BY TOOL STORE' THEN 1 ELSE 0 END) AS superior_approval, " .
            "SUM(CASE WHEN $mile = 'SUPERIOR APPROVED' THEN 1 ELSE 0 END) AS service_admin, " .
            "SUM(CASE WHEN $mile = 'REVIEWED BY SERVICE ADMIN' THEN 1 ELSE 0 END) AS dept_head, " .
            "SUM(CASE WHEN $mile = 'APPROVED BY SERVICE DEPT. HEAD' THEN 1 ELSE 0 END) AS counter_ga, " .
            "SUM(CASE WHEN $norm IN (" .
            "'RECEIVED BY WH/GA'," .
            "'PARTIAL RECEIVED BY WH/GA'," .
            "'PARTIAL RECEIVED TOOL STORE'," .
            "'PARTIAL RECEIVED BY TOOL STORE'" .
            ") THEN 1 ELSE 0 END) AS tool_received_wh_ga, " .
            "SUM(CASE WHEN TRIM(IFNULL(form_milestone,'')) = '' " .
            "OR $mile IN (" .
            "'CHECK BY TOOL STORE'," .
            "'SUPERIOR APPROVED'," .
            "'REVIEWED BY SERVICE ADMIN'," .
            "'APPROVED BY SERVICE DEPT. HEAD'" .
            ") OR $norm IN (" .
            "'RECEIVED BY WH/GA'," .
            "'PARTIAL RECEIVED BY WH/GA'," .
            "'PARTIAL RECEIVED TOOL STORE'," .
            "'PARTIAL RECEIVED BY TOOL STORE'" .
            ") THEN 1 ELSE 0 END) AS notification_total " .
            "FROM $table";
        $query = $this->db_query($sql);
        return $query;
    }

    public function add_form(
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
    ) {
        return $this->db_insert($this->tb_form, [
            "id_form" => $id_form,
            "form_no" => $form_no,
            "form_serv_name" => $form_serv_name,
            "form_check_by" => $form_check_by,
            "form_date_serv_name" => $form_date_serv_name,
            "form_serv_comment" => $form_serv_comment,
            "form_superior_aprd" => $form_superior_aprd,
            "form_superior_comment" => $form_superior_comment,
            "form_sadmin_comment" => $form_sadmin_comment,
            "form_shead_aprd" => $form_shead_aprd,
            "form_shead_comment" => $form_shead_comment,
            "form_date_check_by" => $form_date_check_by,
            "from_date_update" => $from_date_update,
            "form_user_update" => $form_user_update,
            "form_date_superior_aprd" => $form_date_superior_aprd,
            "form_date_sadmin_comment" => $form_date_sadmin_comment,
            "form_date_shead_aprd" => $form_date_shead_aprd,
            "form_milestone" => $form_milestone,
            "form_status_order" => $form_status_order,
        ]);
    }

    public function edit_form(
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
    ) {
        return $this->db_update(
            $this->tb_form,
            [
                "form_no" => $form_no,
                "form_serv_name" => $form_serv_name,
                "form_check_by" => $form_check_by,
                "form_date_serv_name" => $form_date_serv_name,
                "form_serv_comment" => $form_serv_comment,
                "form_superior_aprd" => $form_superior_aprd,
                "form_superior_comment" => $form_superior_comment,
                "form_sadmin_comment" => $form_sadmin_comment,
                "form_shead_aprd" => $form_shead_aprd,
                "form_shead_comment" => $form_shead_comment,
                "form_date_check_by" => $form_date_check_by,
                "from_date_update" => $from_date_update,
                "form_user_update" => $form_user_update,
                "form_date_superior_aprd" => $form_date_superior_aprd,
                "form_date_sadmin_comment" => $form_date_sadmin_comment,
                "form_date_shead_aprd" => $form_date_shead_aprd,
                "form_milestone" => $form_milestone,
                "form_status_order" => $form_status_order,
            ],
            "id_form",
            $id_form,
        );
    }

    public function delete_form(
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
    ) {
        return $this->db_delete_where($this->tb_form, "id_form", $id_form);
    }
}

<?php

require_once __DIR__ . "/../conn/env_loader.php";
require_once __DIR__ . "/db_statement.php";

class DbTable
{
    protected $tb_user = "tb_users";
    protected $tb_action_note = "tb_action_note";
    protected $tb_form = "tb_form";
    protected $tb_form_detail = "tb_form_detail";
    protected $tb_po = "tb_po";
    protected $tb_so = "tb_so";
    protected $tb_superior = "tb_superior";
    protected $tb_rcv_tool = "tb_rcv_tool";
    protected $tb_rcv_wh = "tb_rcv_wh";

    protected $sql_select_distinct = "SELECT DISTINCT ";
    protected $sql_select = "SELECT * FROM ";
    protected $sql_insert = "INSERT INTO ";
    protected $sql_update = "UPDATE ";
    protected $sql_delete = "DELETE FROM ";
    protected $sql_select_count = "SELECT COUNT";
    protected $sql_select_sum = "SELECT SUM";
}

class Proses_sql extends DbTable
{
    use DbStatementTrait;

    private $mysqli;

    function __construct($conn)
    {
        $this->mysqli = $conn;
    }

    // ------------- LOGIN USER ----------------------------

    public function find_user_by_username($username = null)
    {
        $result = null;
        if ($username !== null && $username !== "") {
            $table = $this->tb_user;
            $sup = $this->tb_superior;
            $sql = "SELECT u.*, s.nama_superior AS nama_superior FROM $table u ";
            $sql .= "LEFT JOIN $sup s ON u.superior_id = s.superior_id ";
            $sql .= "WHERE u.username = ? LIMIT 1";
            $db = $this->mysqli->conn;
            $query = $db->prepare($sql);
            if ($query === false) {
                sql_fail($db);
            }
            $query->bind_param("s", $username);
            if ($query->execute()) {
                $result = $query->get_result();
            }
        }
        return $result;
    }

    public function update_user_password($id_users, $passwordHash)
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sql = "UPDATE $table SET password = ? WHERE id_users = ?";
        $query = $db->prepare($sql);
        if ($query === false) {
            sql_fail($db);
        }
        $query->bind_param("ss", $passwordHash, $id_users);
        return $query->execute();
    }

    public function verify_api_token($id_users, $token)
    {
        if ($id_users === "" || $token === "") {
            return false;
        }
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sql = "SELECT id_users FROM $table WHERE id_users = ? AND token = ? LIMIT 1";
        $query = $db->prepare($sql);
        if ($query === false) {
            sql_fail($db);
        }
        $query->bind_param("ss", $id_users, $token);
        if (!$query->execute()) {
            return false;
        }
        $result = $query->get_result();
        return $result && $result->num_rows > 0;
    }

    // ------------- TABEL USER ----------------------------

    public function data_user(
        $id_users,
        $username,
        $password,
        $nama_user,
        $foto,
        $id_tu,
        $no_telp,
        $token,
        $level,
        $status,
        $superior_id,
        $limit = null,
        $offset = 0,
        $search = null,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sup = $this->tb_superior;
        $sql = "SELECT u.*, s.nama_superior AS nama_superior, sup_u.no_telp AS no_telp_superior FROM $table u ";
        $sql .= "LEFT JOIN $sup s ON u.superior_id = s.superior_id ";
        $sql .= "LEFT JOIN $table sup_u ON sup_u.nama_user = s.nama_superior ";
        $search_trim = trim((string) ($search ?? ""));
        if ((@$id_users ?? "") !== "") {
            $sql .= " WHERE u.id_users = ? ";
            $params = [(string) $id_users];
            $types = "s";
        } elseif ($search_trim !== "") {
            $like = "%" . $search_trim . "%";
            $sql .= " WHERE (u.username LIKE ? OR u.nama_user LIKE ? OR u.no_telp LIKE ? OR u.level LIKE ? OR IFNULL(u.status,'') LIKE ? OR CAST(u.id_users AS CHAR) LIKE ? OR IFNULL(s.nama_superior,'') LIKE ?) ";
            $sql .= " ORDER BY u.nama_user ASC";
            $types = "sssssss";
            $params = [$like, $like, $like, $like, $like, $like, $like];
            if ($limit !== null && (int) $limit > 0) {
                $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
            }
        } elseif ((@$nama_user ?? "") !== "") {
            $sql .= " WHERE u.nama_user = ? ";
            $params = [(string) $nama_user];
            $types = "s";
        } elseif ((@$username ?? "") !== "") {
            $sql .= " WHERE u.username = ? ";
            $params = [(string) $username];
            $types = "s";
        } elseif ((@$superior_id ?? "") !== "") {
            $sql .= " WHERE u.superior_id = ? ";
            $params = [(string) $superior_id];
            $types = "s";
        } else {
            $query = $this->db_query($sql . " ORDER BY u.nama_user ASC" .
                ($limit !== null && (int) $limit > 0
                    ? " LIMIT " . (int) $limit . " OFFSET " . (int) $offset
                    : ""));
            return $query;
        }
        return $this->db_run_select($sql, $types, $params);
    }

    /**
     * Jumlah baris user yang cocok dengan filter yang sama seperti data_user(),
     * tanpa LIMIT/OFFSET (untuk pagination di cont_user.php).
     */
    public function count_user(
        $id_users,
        $username,
        $nama_user,
        $superior_id,
        $search = null,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sup = $this->tb_superior;
        $sql = "SELECT COUNT(*) AS cnt FROM $table u ";
        $sql .= "LEFT JOIN $sup s ON u.superior_id = s.superior_id ";
        $sql .= "LEFT JOIN $table sup_u ON sup_u.nama_user = s.nama_superior ";
        $search_trim = trim((string) ($search ?? ""));
        if ((@$id_users ?? "") !== "") {
            $sql .= " WHERE u.id_users = ? ";
            $types = "s";
            $params = [(string) $id_users];
        } elseif ($search_trim !== "") {
            $like = "%" . $search_trim . "%";
            $sql .= " WHERE (u.username LIKE ? OR u.nama_user LIKE ? OR u.no_telp LIKE ? OR u.level LIKE ? OR IFNULL(u.status,'') LIKE ? OR CAST(u.id_users AS CHAR) LIKE ? OR IFNULL(s.nama_superior,'') LIKE ?) ";
            $types = "sssssss";
            $params = [$like, $like, $like, $like, $like, $like, $like];
        } elseif ((@$nama_user ?? "") !== "") {
            $sql .= " WHERE u.nama_user = ? ";
            $types = "s";
            $params = [(string) $nama_user];
        } elseif ((@$username ?? "") !== "") {
            $sql .= " WHERE u.username = ? ";
            $types = "s";
            $params = [(string) $username];
        } elseif ((@$superior_id ?? "") !== "") {
            $sql .= " WHERE u.superior_id = ? ";
            $types = "s";
            $params = [(string) $superior_id];
        } else {
            return $this->db_query($sql);
        }
        return $this->db_run_select($sql, $types, $params);
    }

    public function add_user(
        $id_users,
        $username,
        $password,
        $nama_user,
        $foto,
        $id_tu,
        $no_telp,
        $token,
        $level,
        $status,
        $superior_id,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sql = "INSERT INTO $table (id_users, username, password, nama_user, foto, id_tu, no_telp, token, level, status, superior_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $db->prepare($sql);
        if ($query === false) {
            sql_fail($db);
        }
        $query->bind_param(
            "sssssssssss",
            $id_users,
            $username,
            $password,
            $nama_user,
            $foto,
            $id_tu,
            $no_telp,
            $token,
            $level,
            $status,
            $superior_id,
        );
        if (!$query->execute()) {
            sql_fail($db);
        }
        return $query;
    }

    public function edit_user(
        $id_users,
        $username,
        $password,
        $nama_user,
        $foto,
        $id_tu,
        $no_telp,
        $token,
        $level,
        $status,
        $superior_id,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sql = "UPDATE $table SET username = ?, password = ?, nama_user = ?, foto = ?, id_tu = ?, no_telp = ?, token = ?, level = ?, status = ?, superior_id = ? WHERE id_users = ?";
        $query = $db->prepare($sql);
        if ($query === false) {
            sql_fail($db);
        }
        $query->bind_param(
            "sssssssssss",
            $username,
            $password,
            $nama_user,
            $foto,
            $id_tu,
            $no_telp,
            $token,
            $level,
            $status,
            $superior_id,
            $id_users,
        );
        if (!$query->execute()) {
            sql_fail($db);
        }
        return $query;
    }

    public function delete_user(
        $id_users,
        $username,
        $password,
        $nama_user,
        $foto,
        $id_tu,
        $no_telp,
        $token,
        $level,
        $status,
        $superior_id,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sql = "DELETE FROM $table WHERE id_users = ?";
        $query = $db->prepare($sql);
        if ($query === false) {
            sql_fail($db);
        }
        $query->bind_param("s", $id_users);
        if (!$query->execute()) {
            sql_fail($db);
        }
        return $query;
    }

    public function get_next_user_id()
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sql = "SELECT COALESCE(MAX(id_users), 0) + 1 AS next_id FROM $table";
        $query = $this->db_query($sql);
        $row = $query->fetch_object();
        return (int) ($row->next_id ?? 1);
    }

    public function user_id_exists($id_users)
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sql = "SELECT id_users FROM $table WHERE id_users = ? LIMIT 1";
        $query = $db->prepare($sql);
        if ($query === false) {
            sql_fail($db);
        }
        $query->bind_param("s", $id_users);
        if (!$query->execute()) {
            sql_fail($db);
        }
        $result = $query->get_result();
        return $result && $result->num_rows > 0;
    }

    public function username_exists($username, $exclude_id_users = "")
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        if ((string) $exclude_id_users !== "") {
            $sql = "SELECT id_users FROM $table WHERE username = ? AND id_users != ? LIMIT 1";
            $query = $db->prepare($sql);
            if ($query === false) {
                sql_fail($db);
            }
            $query->bind_param("ss", $username, $exclude_id_users);
        } else {
            $sql = "SELECT id_users FROM $table WHERE username = ? LIMIT 1";
            $query = $db->prepare($sql);
            if ($query === false) {
                sql_fail($db);
            }
            $query->bind_param("s", $username);
        }
        if (!$query->execute()) {
            sql_fail($db);
        }
        $result = $query->get_result();
        return $result && $result->num_rows > 0;
    }

    // ------------- TABEL FORM ----------------------------

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

    // ------------- TABEL FORM DETAIL ----------------------

    public function data_form_detail(
        $id_form_detail,
        $id_form,
        $form_comment,
        $pn_group,
        $pn_desc,
        $qty,
        $explan,
        $action_note,
        $val_type,
        $part_value,
        $form_detail_milestone,
        $form_detail_date,
        $form_detail_user,
    ) {
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_form_detail,
                "id_form_detail",
                $id_form_detail,
            );
        }
        if ((@$id_form ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_form_detail,
                "id_form",
                $id_form,
                "id_form ASC",
            );
        }
        return $this->db_query(
            $this->sql_select . $this->tb_form_detail . " ORDER BY id_form ASC",
        );
    }

    public function add_form_detail(
        $id_form_detail,
        $id_form,
        $form_comment,
        $pn_group,
        $pn_desc,
        $qty,
        $explan,
        $action_note,
        $val_type,
        $part_value,
        $form_detail_milestone,
        $form_detail_date,
        $form_detail_user,
    ) {
        return $this->db_insert($this->tb_form_detail, [
            "id_form_detail" => $id_form_detail,
            "id_form" => $id_form,
            "form_comment" => $form_comment,
            "pn_group" => $pn_group,
            "pn_desc" => $pn_desc,
            "qty" => $qty,
            "explan" => $explan,
            "action_note" => $action_note,
            "val_type" => $val_type,
            "part_value" => $part_value,
            "form_detail_milestone" => $form_detail_milestone,
            "form_detail_date" => $form_detail_date,
            "form_detail_user" => $form_detail_user,
        ]);
    }

    public function edit_form_detail(
        $id_form_detail,
        $id_form,
        $form_comment,
        $pn_group,
        $pn_desc,
        $qty,
        $explan,
        $action_note,
        $val_type,
        $part_value,
        $form_detail_milestone,
        $form_detail_date,
        $form_detail_user,
    ) {
        return $this->db_update(
            $this->tb_form_detail,
            [
                "id_form" => $id_form,
                "form_comment" => $form_comment,
                "pn_group" => $pn_group,
                "pn_desc" => $pn_desc,
                "qty" => $qty,
                "explan" => $explan,
                "action_note" => $action_note,
                "val_type" => $val_type,
                "part_value" => $part_value,
                "form_detail_milestone" => $form_detail_milestone,
                "form_detail_date" => $form_detail_date,
                "form_detail_user" => $form_detail_user,
            ],
            "id_form_detail",
            $id_form_detail,
        );
    }

    public function delete_form_detail(
        $id_form_detail,
        $id_form,
        $form_comment,
        $pn_group,
        $pn_desc,
        $qty,
        $explan,
        $action_note,
        $val_type,
        $part_value,
        $form_detail_milestone,
        $form_detail_date,
        $form_detail_user,
    ) {
        return $this->db_delete_where(
            $this->tb_form_detail,
            "id_form_detail",
            $id_form_detail,
        );
    }
    // ------------- TABEL ACTION NOTE --------------------

    public function data_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        if ((@$id_action_note ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_action_note,
                "id_action_note",
                $id_action_note,
            );
        }
        if ((@$action_note_desc ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_action_note,
                "action_note_desc",
                $action_note_desc,
            );
        }
        return $this->db_query(
            $this->sql_select .
                $this->tb_action_note .
                " ORDER BY id_action_note ASC",
        );
    }

    public function add_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->db_insert($this->tb_action_note, [
            "id_action_note" => $id_action_note,
            "note_initial" => $note_initial,
            "action_note_desc" => $action_note_desc,
            "action_date_update" => $action_date_update,
            "action_note_user" => $action_note_user,
        ]);
    }

    public function edit_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->db_update(
            $this->tb_action_note,
            [
                "action_note_desc" => $action_note_desc,
                "note_initial" => $note_initial,
                "action_date_update" => $action_date_update,
                "action_note_user" => $action_note_user,
            ],
            "id_action_note",
            $id_action_note,
        );
    }

    public function delete_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->db_delete_where(
            $this->tb_action_note,
            "id_action_note",
            $id_action_note,
        );
    }

    // ------------- TABEL PO ----------------------

    public function data_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        if ((@$id_po ?? "") !== "") {
            return $this->db_select_where($this->tb_po, "id_po", $id_po);
        }
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_po,
                "id_form_detail",
                $id_form_detail,
                "id_form_detail ASC",
            );
        }
        if ((@$po_no ?? "") !== "") {
            return $this->db_select_where($this->tb_po, "po_no", $po_no);
        }
        return $this->db_query(
            $this->sql_select . $this->tb_po . " ORDER BY id_form_detail ASC",
        );
    }

    public function add_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->db_insert($this->tb_po, [
            "id_po" => $id_po,
            "id_form_detail" => $id_form_detail,
            "po_no" => $po_no,
            "date_update_po" => $date_update_po,
            "user_update_po" => $user_update_po,
        ]);
    }

    public function edit_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->db_update(
            $this->tb_po,
            [
                "id_form_detail" => $id_form_detail,
                "po_no" => $po_no,
                "date_update_po" => $date_update_po,
                "user_update_po" => $user_update_po,
            ],
            "id_po",
            $id_po,
        );
    }

    public function delete_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->db_delete_where($this->tb_po, "id_po", $id_po);
    }

    // ------------- TABEL SO ----------------------

    public function data_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        if ((@$id_so ?? "") !== "") {
            return $this->db_select_where($this->tb_so, "id_so", $id_so);
        }
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_so,
                "id_form_detail",
                $id_form_detail,
                "id_form_detail ASC",
            );
        }
        if ((@$so ?? "") !== "") {
            return $this->db_select_where($this->tb_so, "so", $so);
        }
        return $this->db_query(
            $this->sql_select . $this->tb_so . " ORDER BY id_form_detail ASC",
        );
    }

    public function add_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        return $this->db_insert($this->tb_so, [
            "id_so" => $id_so,
            "id_form_detail" => $id_form_detail,
            "so" => $so,
            "eta" => $eta,
            "note_so" => $note_so,
            "date_update_so" => $date_update_so,
            "id_update_so" => $id_update_so,
        ]);
    }

    public function edit_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        return $this->db_update(
            $this->tb_so,
            [
                "id_form_detail" => $id_form_detail,
                "so" => $so,
                "eta" => $eta,
                "note_so" => $note_so,
                "date_update_so" => $date_update_so,
                "id_update_so" => $id_update_so,
            ],
            "id_so",
            $id_so,
        );
    }

    public function delete_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        return $this->db_delete_where($this->tb_so, "id_so", $id_so);
    }

    // ------------- TABEL SUPERRIOR ----------------------

    public function data_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        if ((@$superior_id ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_superior,
                "superior_id",
                $superior_id,
            );
        }
        if ((@$nama_superior ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_superior,
                "nama_superior",
                $nama_superior,
            );
        }
        return $this->db_query(
            $this->sql_select .
                $this->tb_superior .
                " ORDER BY nama_superior ASC",
        );
    }

    public function add_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        return $this->db_insert($this->tb_superior, [
            "superior_id" => $superior_id,
            "nama_superior" => $nama_superior,
            "status_superior" => $status_superior,
            "user_id_input_superior" => $user_id_input_superior,
            "date_input_superior" => $date_input_superior,
        ]);
    }

    public function edit_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        return $this->db_update(
            $this->tb_superior,
            [
                "nama_superior" => $nama_superior,
                "status_superior" => $status_superior,
                "user_id_input_superior" => $user_id_input_superior,
                "date_input_superior" => $date_input_superior,
            ],
            "superior_id",
            $superior_id,
        );
    }

    public function delete_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        return $this->db_delete_where(
            $this->tb_superior,
            "superior_id",
            $superior_id,
        );
    }

    public function get_next_superior_id()
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $sql = "SELECT COALESCE(MAX(superior_id), 0) + 1 AS next_id FROM $table";
        $query = $this->db_query($sql);
        $row = $query->fetch_object();
        return (int) ($row->next_id ?? 1);
    }

    public function superior_id_exists($superior_id)
    {
        $result = $this->db_run_select(
            "SELECT superior_id FROM {$this->tb_superior} WHERE superior_id = ? LIMIT 1",
            "s",
            [(string) $superior_id],
        );
        return $result && $result->num_rows > 0;
    }

    public function nama_superior_exists(
        $nama_superior,
        $exclude_superior_id = "",
    ) {
        if ((string) $exclude_superior_id !== "") {
            $result = $this->db_run_select(
                "SELECT superior_id FROM {$this->tb_superior} WHERE nama_superior = ? AND superior_id != ? LIMIT 1",
                "ss",
                [(string) $nama_superior, (string) $exclude_superior_id],
            );
        } else {
            $result = $this->db_run_select(
                "SELECT superior_id FROM {$this->tb_superior} WHERE nama_superior = ? LIMIT 1",
                "s",
                [(string) $nama_superior],
            );
        }
        return $result && $result->num_rows > 0;
    }

    // ------------- TABEL RCV WH ----------------------

    public function data_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        if ((@$id_rcv_wh ?? "") !== "") {
            return $this->db_select_where($this->tb_rcv_wh, "id_rcv_wh", $id_rcv_wh);
        }
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_rcv_wh,
                "id_form_detail",
                $id_form_detail,
                "id_form_detail ASC",
            );
        }
        return $this->db_query(
            $this->sql_select . $this->tb_rcv_wh . " ORDER BY id_form_detail ASC",
        );
    }

    public function add_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->db_insert($this->tb_rcv_wh, [
            "id_rcv_wh" => $id_rcv_wh,
            "id_form_detail" => $id_form_detail,
            "rcv_wh_date" => $rcv_wh_date,
            "rcv_wh_id_input" => $rcv_wh_id_input,
            "rcv_wh_date_input" => $rcv_wh_date_input,
        ]);
    }

    public function edit_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->db_update(
            $this->tb_rcv_wh,
            [
                "id_form_detail" => $id_form_detail,
                "rcv_wh_date" => $rcv_wh_date,
                "rcv_wh_id_input" => $rcv_wh_id_input,
                "rcv_wh_date_input" => $rcv_wh_date_input,
            ],
            "id_rcv_wh",
            $id_rcv_wh,
        );
    }

    public function delete_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->db_delete_where($this->tb_rcv_wh, "id_rcv_wh", $id_rcv_wh);
    }

    // ------------- TABEL RCV TOOL ----------------------

    public function data_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        if ((@$id_rcv_tool ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_rcv_tool,
                "id_rcv_tool",
                $id_rcv_tool,
            );
        }
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_rcv_tool,
                "id_form_detail",
                $id_form_detail,
                "id_form_detail ASC",
            );
        }
        return $this->db_query(
            $this->sql_select .
                $this->tb_rcv_tool .
                " ORDER BY id_form_detail ASC",
        );
    }

    public function add_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        return $this->db_insert($this->tb_rcv_tool, [
            "id_rcv_tool" => $id_rcv_tool,
            "id_form_detail" => $id_form_detail,
            "rcv_tool_date" => $rcv_tool_date,
            "rcv_tool_id_input" => $rcv_tool_id_input,
            "rcv_tool_date_input" => $rcv_tool_date_input,
        ]);
    }

    public function edit_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        return $this->db_update(
            $this->tb_rcv_tool,
            [
                "id_form_detail" => $id_form_detail,
                "rcv_tool_date" => $rcv_tool_date,
                "rcv_tool_id_input" => $rcv_tool_id_input,
                "rcv_tool_date_input" => $rcv_tool_date_input,
            ],
            "id_rcv_tool",
            $id_rcv_tool,
        );
    }

    public function delete_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        return $this->db_delete_where(
            $this->tb_rcv_tool,
            "id_rcv_tool",
            $id_rcv_tool,
        );
    }

    function __destruct()
    {
        if (
            isset($this->mysqli->conn) &&
            $this->mysqli->conn instanceof mysqli
        ) {
            $this->mysqli->conn->close();
        }
    }
}
?>

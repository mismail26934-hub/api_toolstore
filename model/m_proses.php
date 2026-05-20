<?php
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
    private $mysqli;

    function __construct($conn)
    {
        $this->mysqli = $conn;
    }

    // ------------- LOGIN USER ----------------------------

    public function login($username = null, $password = null)
    {
        $result = null;
        if (
            $username !== null &&
            $username !== "" &&
            $password !== null &&
            $password !== ""
        ) {
            $table = $this->tb_user;
            $sup = $this->tb_superior;
            $sql = "SELECT u.*, s.nama_superior AS nama_superior FROM $table u ";
            $sql .= "LEFT JOIN $sup s ON u.superior_id = s.superior_id ";
            $sql .= "WHERE u.username = ? AND u.password = ?";
            $db = $this->mysqli->conn;
            ($query = $db->prepare($sql)) or die($db->error);
            $query->bind_param("ss", $username, $password);
            if ($query->execute()) {
                $result = $query->get_result();
            }
        }
        return $result;
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
            $sql .= " WHERE u.id_users = '$id_users' ";
        } elseif ($search_trim !== "") {
            $escaped = $db->real_escape_string($search_trim);
            $sql .=
                " WHERE (u.username LIKE '%$escaped%' OR u.nama_user LIKE '%$escaped%' OR u.no_telp LIKE '%$escaped%' OR u.level LIKE '%$escaped%' OR IFNULL(u.status,'') LIKE '%$escaped%' OR CAST(u.id_users AS CHAR) LIKE '%$escaped%' OR IFNULL(s.nama_superior,'') LIKE '%$escaped%') ";
            $sql .= " ORDER BY u.nama_user ASC";
            if ($limit !== null && (int) $limit > 0) {
                $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
            }
        } elseif ((@$nama_user ?? "") !== "") {
            $sql .= " WHERE u.nama_user = '$nama_user' ";
        } elseif ((@$username ?? "") !== "") {
            $sql .= " WHERE u.username = '$username' ";
        } elseif ((@$superior_id ?? "") !== "") {
            $sql .= " WHERE u.superior_id = '$superior_id' ";
        } else {
            $sql .= " ORDER BY u.nama_user ASC";
            if ($limit !== null && (int) $limit > 0) {
                $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
            }
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
            $sql .= " WHERE u.id_users = '$id_users' ";
        } elseif ($search_trim !== "") {
            $escaped = $db->real_escape_string($search_trim);
            $sql .=
                " WHERE (u.username LIKE '%$escaped%' OR u.nama_user LIKE '%$escaped%' OR u.no_telp LIKE '%$escaped%' OR u.level LIKE '%$escaped%' OR IFNULL(u.status,'') LIKE '%$escaped%' OR CAST(u.id_users AS CHAR) LIKE '%$escaped%' OR IFNULL(s.nama_superior,'') LIKE '%$escaped%') ";
        } elseif ((@$nama_user ?? "") !== "") {
            $sql .= " WHERE u.nama_user = '$nama_user' ";
        } elseif ((@$username ?? "") !== "") {
            $sql .= " WHERE u.username = '$username' ";
        } elseif ((@$superior_id ?? "") !== "") {
            $sql .= " WHERE u.superior_id = '$superior_id' ";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            id_users = '$id_users',
            username = '$username',
            password = '$password',
            nama_user = '$nama_user',
            foto = '$foto',
            id_tu = '$id_tu',
            no_telp = '$no_telp',
            token = '$token',
            level = '$level',
            status = '$status',
            superior_id = '$superior_id'
            
            ";

        ($query = $db->query($sql)) or die($db->error);
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
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET 
            username = '$username',
            password = '$password',
            nama_user = '$nama_user',
            foto = '$foto',
            id_tu = '$id_tu',
            no_telp = '$no_telp',
            token = '$token',
            level = '$level',
            status = '$status',
            superior_id = '$superior_id'
            WHERE id_users = '$id_users'
            ";

        ($query = $db->query($sql)) or die($db->error);
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
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_users = '$id_users'";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function get_next_user_id()
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $sql = "SELECT COALESCE(MAX(id_users), 0) + 1 AS next_id FROM $table";
        ($query = $db->query($sql)) or die($db->error);
        $row = $query->fetch_object();
        return (int) ($row->next_id ?? 1);
    }

    public function user_id_exists($id_users)
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $id_users = $db->real_escape_string((string) $id_users);
        $sql = "SELECT id_users FROM $table WHERE id_users = '$id_users' LIMIT 1";
        ($query = $db->query($sql)) or die($db->error);
        return $query->num_rows > 0;
    }

    public function username_exists($username, $exclude_id_users = "")
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $username = $db->real_escape_string((string) $username);
        $sql = "SELECT id_users FROM $table WHERE username = '$username'";
        if ((string) $exclude_id_users !== "") {
            $exclude_id_users = $db->real_escape_string((string) $exclude_id_users);
            $sql .= " AND id_users != '$exclude_id_users'";
        }
        $sql .= " LIMIT 1";
        ($query = $db->query($sql)) or die($db->error);
        return $query->num_rows > 0;
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
                return " WHERE " . $detail_match("fd.pn_group LIKE $like") . " ";
            case "pndesc":
            case "pn_desc":
                return " WHERE " . $detail_match("fd.pn_desc LIKE $like") . " ";
            case "all":
            default:
                return
                    " WHERE (form_no LIKE $like OR form_serv_name LIKE $like OR form_serv_comment LIKE $like " .
                    "OR CAST(id_form AS CHAR) LIKE $like OR IFNULL(form_check_by,'') LIKE $like " .
                    "OR IFNULL(form_milestone,'') LIKE $like OR " .
                    $detail_match("fd.pn_group LIKE $like OR fd.pn_desc LIKE $like") .
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
        if ((@$id_form ?? "") === "" &&
            (@$form_no ?? "") === "" &&
            (@$form_serv_name ?? "") === "") {
            $sql .= " ORDER BY form_no ASC";
            if ($limit !== null && (int) $limit > 0) {
                $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
            }
        }
        ($query = $db->query($sql)) or die($db->error);
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
        ($query = $db->query($sql)) or die($db->error);
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
        $norm =
            "UPPER(TRIM(REPLACE(IFNULL(form_milestone,''), '.', '')))";
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
        ($query = $db->query($sql)) or die($db->error);
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
        $db = $this->mysqli->conn;
        $table = $this->tb_form;
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            id_form = '$id_form',
            form_no = '$form_no',
            form_serv_name = '$form_serv_name',
            form_check_by = '$form_check_by',
            form_date_serv_name = '$form_date_serv_name',
            form_serv_comment = '$form_serv_comment',
            form_superior_aprd = '$form_superior_aprd',
            form_superior_comment = '$form_superior_comment',
            form_sadmin_comment = '$form_sadmin_comment',
            form_shead_aprd = '$form_shead_aprd',
            form_shead_comment = '$form_shead_comment',
            form_date_check_by = '$form_date_check_by',
            from_date_update = '$from_date_update',
            form_user_update = '$form_user_update',
            form_date_superior_aprd = '$form_date_superior_aprd',
            form_date_sadmin_comment = '$form_date_sadmin_comment',
            form_date_shead_aprd = '$form_date_shead_aprd',
            form_milestone = '$form_milestone',
            form_status_order = '$form_status_order'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_form;
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET 
            form_no = '$form_no',
            form_serv_name = '$form_serv_name',
            form_check_by = '$form_check_by',
            form_date_serv_name = '$form_date_serv_name',
            form_serv_comment = '$form_serv_comment',
            form_superior_aprd = '$form_superior_aprd',
            form_superior_comment = '$form_superior_comment',
            form_sadmin_comment = '$form_sadmin_comment',
            form_shead_aprd = '$form_shead_aprd',
            form_shead_comment = '$form_shead_comment',
            form_date_check_by = '$form_date_check_by',
            from_date_update = '$from_date_update',
            form_user_update = '$form_user_update',
            form_date_superior_aprd = '$form_date_superior_aprd',
            form_date_sadmin_comment = '$form_date_sadmin_comment',
            form_date_shead_aprd = '$form_date_shead_aprd',
            form_milestone = '$form_milestone',
            form_status_order = '$form_status_order'
            WHERE id_form = '$id_form'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_form;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_form = '$id_form'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_form_detail;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if ((@$id_form_detail ?? "") !== "") {
            $sql .= " WHERE id_form_detail = '$id_form_detail' ";
        } elseif ((@$id_form ?? "") !== "") {
            $sql .= " WHERE id_form = '$id_form' ";
        } else {
            $sql .= " ORDER BY id_form ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_form_detail;
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            id_form_detail = '$id_form_detail',
            id_form = '$id_form',
            form_comment = '$form_comment',
            pn_group = '$pn_group',
            pn_desc = '$pn_desc',
            qty = '$qty',
            explan = '$explan',
            action_note = '$action_note',
            val_type = '$val_type',
            part_value = '$part_value',
            form_detail_milestone = '$form_detail_milestone',
            form_detail_date = '$form_detail_date',
            form_detail_user = '$form_detail_user'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_form_detail;
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET 
            id_form = '$id_form',
            form_comment = '$form_comment',
            pn_group = '$pn_group',
            pn_desc = '$pn_desc',
            qty = '$qty',
            explan = '$explan',
            action_note = '$action_note',
            val_type = '$val_type',
            part_value = '$part_value',     
            form_detail_milestone = '$form_detail_milestone',     
            form_detail_date = '$form_detail_date',
            form_detail_user = '$form_detail_user'
            WHERE id_form_detail = '$id_form_detail'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_form_detail;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_form_detail = '$id_form_detail'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }
    // ------------- TABEL ACTION NOTE --------------------

    public function data_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_action_note;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if ((@$id_action_note ?? "") !== "") {
            $sql .= " WHERE id_action_note = '$id_action_note' ";
        } elseif ((@$action_note_desc ?? "") !== "") {
            $sql .= " WHERE action_note_desc = '$action_note_desc' ";
        } else {
            $sql .= " ORDER BY id_action_note ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function add_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_action_note;
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            id_action_note = '$id_action_note',
            note_initial = '$note_initial',
            action_note_desc = '$action_note_desc',
            action_date_update = '$action_date_update',
            action_note_user = '$action_note_user'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_action_note;
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET
            action_note_desc = '$action_note_desc',
            note_initial = '$note_initial',
            action_date_update = '$action_date_update',
            action_note_user = '$action_note_user'
            WHERE id_action_note = '$id_action_note'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_action_note;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_action_note = '$id_action_note'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    // ------------- TABEL PO ----------------------

    public function data_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_po;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if ((@$id_po ?? "") !== "") {
            $sql .= " WHERE id_po = '$id_po' ";
        } elseif ((@$id_form_detail ?? "") !== "") {
            $sql .= " WHERE id_form_detail = '$id_form_detail' ";
        } elseif ((@$po_no ?? "") !== "") {
            $sql .= " WHERE po_no = '$po_no' ";
        } else {
            $sql .= " ORDER BY id_form_detail ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function add_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_po;
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            id_po = '$id_po',
            id_form_detail = '$id_form_detail',
            po_no = '$po_no',
            date_update_po = '$date_update_po',
            user_update_po = '$user_update_po'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_po;
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET
            id_form_detail = '$id_form_detail',
            po_no = '$po_no',
            date_update_po = '$date_update_po',
            user_update_po = '$user_update_po'
            WHERE id_po = '$id_po'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_po;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_po = '$id_po'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_so;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if ((@$id_so ?? "") !== "") {
            $sql .= " WHERE id_so = '$id_so' ";
        } elseif ((@$id_form_detail ?? "") !== "") {
            $sql .= " WHERE id_form_detail = '$id_form_detail' ";
        } elseif ((@$so ?? "") !== "") {
            $sql .= " WHERE so = '$so' ";
        } else {
            $sql .= " ORDER BY id_form_detail ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_so;
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            id_so = '$id_so',
            id_form_detail = '$id_form_detail',
            so = '$so',
            eta = '$eta',
            note_so = '$note_so',
            date_update_so = '$date_update_so',
            id_update_so = '$id_update_so'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_so;
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET
            id_form_detail = '$id_form_detail',
            so = '$so',
            eta = '$eta',
            note_so = '$note_so',
            date_update_so = '$date_update_so',
            id_update_so = '$id_update_so'
            WHERE id_so = '$id_so'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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
        $db = $this->mysqli->conn;
        $table = $this->tb_so;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_so = '$id_so'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    // ------------- TABEL SUPERRIOR ----------------------

    public function data_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if ((@$superior_id ?? "") !== "") {
            $sql .= " WHERE superior_id = '$superior_id' ";
        } elseif ((@$nama_superior ?? "") !== "") {
            $sql .= " WHERE nama_superior = '$nama_superior' ";
        } else {
            $sql .= " ORDER BY nama_superior ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function add_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            superior_id = '$superior_id',
            nama_superior = '$nama_superior',
            status_superior = '$status_superior',
            user_id_input_superior = '$user_id_input_superior',
            date_input_superior = '$date_input_superior'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET
            nama_superior = '$nama_superior',
            status_superior = '$status_superior',
            user_id_input_superior = '$user_id_input_superior',
            date_input_superior = '$date_input_superior'
            WHERE superior_id = '$superior_id'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE superior_id = '$superior_id'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function get_next_superior_id()
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $sql = "SELECT COALESCE(MAX(superior_id), 0) + 1 AS next_id FROM $table";
        ($query = $db->query($sql)) or die($db->error);
        $row = $query->fetch_object();
        return (int) ($row->next_id ?? 1);
    }

    public function superior_id_exists($superior_id)
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $superior_id = $db->real_escape_string((string) $superior_id);
        $sql = "SELECT superior_id FROM $table WHERE superior_id = '$superior_id' LIMIT 1";
        ($query = $db->query($sql)) or die($db->error);
        return $query->num_rows > 0;
    }

    public function nama_superior_exists($nama_superior, $exclude_superior_id = "")
    {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $nama_superior = $db->real_escape_string((string) $nama_superior);
        $sql = "SELECT superior_id FROM $table WHERE nama_superior = '$nama_superior'";
        if ((string) $exclude_superior_id !== "") {
            $exclude_superior_id = $db->real_escape_string(
                (string) $exclude_superior_id,
            );
            $sql .= " AND superior_id != '$exclude_superior_id'";
        }
        $sql .= " LIMIT 1";
        ($query = $db->query($sql)) or die($db->error);
        return $query->num_rows > 0;
    }

    // ------------- TABEL RCV WH ----------------------

    public function data_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_rcv_wh;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if ((@$id_rcv_wh ?? "") !== "") {
            $sql .= " WHERE id_rcv_wh = '$id_rcv_wh' ";
        } elseif ((@$id_form_detail ?? "") !== "") {
            $sql .= " WHERE id_form_detail = '$id_form_detail' ";
        } else {
            $sql .= " ORDER BY id_form_detail ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function add_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_rcv_wh;
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            id_rcv_wh = '$id_rcv_wh',
            id_form_detail = '$id_form_detail',
            rcv_wh_date = '$rcv_wh_date',
            rcv_wh_id_input = '$rcv_wh_id_input',
            rcv_wh_date_input = '$rcv_wh_date_input'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_rcv_wh;
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET
            id_form_detail = '$id_form_detail',
            rcv_wh_date = '$rcv_wh_date',
            rcv_wh_id_input = '$rcv_wh_id_input',
            rcv_wh_date_input = '$date_input_superior'
            WHERE id_rcv_wh = '$id_rcv_wh'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_rcv_wh;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_rcv_wh = '$id_rcv_wh'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    // ------------- TABEL RCV TOOL ----------------------

    public function data_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_rcv_tool;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if ((@$id_rcv_tool ?? "") !== "") {
            $sql .= " WHERE id_rcv_tool = '$id_rcv_tool' ";
        } elseif ((@$id_form_detail ?? "") !== "") {
            $sql .= " WHERE id_form_detail = '$id_form_detail' ";
        } else {
            $sql .= " ORDER BY id_form_detail ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function add_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_rcv_tool;
        $insert = $this->sql_insert;
        $sql = $insert;
        $sql .= $table;
        $sql .= " SET 
            id_rcv_tool = '$id_rcv_tool',
            id_form_detail = '$id_form_detail',
            rcv_tool_date = '$rcv_tool_date',
            rcv_tool_id_input = '$rcv_tool_id_input',
            rcv_tool_date_input = '$rcv_tool_date_input'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_rcv_tool;
        $update = $this->sql_update;
        $sql = $update;
        $sql .= $table;
        $sql .= " SET
            id_form_detail = '$id_form_detail',
            rcv_tool_date = '$rcv_tool_date',
            rcv_tool_id_input = '$rcv_tool_id_input',
            rcv_tool_date_input = '$rcv_tool_date_input'
            WHERE id_rcv_tool = '$id_rcv_tool'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_rcv_tool;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_rcv_tool = '$id_rcv_tool'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
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

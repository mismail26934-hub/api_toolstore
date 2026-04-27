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
        if ($username != null && $password != null) {
            $table = $this->tb_user;
            $select = $this->sql_select;
            $sql = $select;
            $sql .= $table;
            $sql .= " WHERE username = ? AND password = ?";
            $db = $this->mysqli->conn;
            ($query = $db->prepare($sql)) or die($db->error);
            $query->bind_param("ss", $username, $password);
            if ($query->execute()) {
                $result = $query->get_result();
            }
        }
        return @$result;
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
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if (@$id_users != null || @$id_users != "") {
            $sql .= " WHERE id_users = '$id_users' ";
        } elseif (@$nama_user != null || @$nama_user != "") {
            $sql .= " WHERE nama_user = '$nama_user' ";
        } elseif (@$username != null || @$username != "") {
            $sql .= " WHERE username = '$username' ";
        } else {
            $sql .= " ORDER BY nama_user ASC";
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

    // ------------- TABEL FORM ----------------------------

    public function data_form(
        $id_from,
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
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_form;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if (@$id_from != null || @$id_from != "") {
            $sql .= " WHERE id_from = '$id_from' ";
        } elseif (@$form_no != null || @$form_no != "") {
            $sql .= " WHERE form_no = '$form_no' ";
        } elseif (@$form_serv_name != null || @$form_serv_name != "") {
            $sql .= " WHERE form_serv_name = '$form_serv_name' ";
        } else {
            $sql .= " ORDER BY form_no ASC LIMIT $limit OFFSET $offset";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function add_form(
        $id_from,
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
            id_from = '$id_from',
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
        $id_from,
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
            WHERE id_from = '$id_from'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_form(
        $id_from,
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
        $sql .= " WHERE id_from = '$id_from'
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
        $form_detail_date,
        $form_detail_user,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_form_detail;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if (@$id_form_detail != null || @$id_form_detail != "") {
            $sql .= " WHERE id_form_detail = '$id_form_detail' ";
        } elseif (@$id_form != null || @$id_form != "") {
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
        $id_form,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_action_note;
        $select = $this->sql_select;
        $sql = $select;
        $sql .= $table;
        if (@$id_action_note != null || @$id_action_note != "") {
            $sql .= " WHERE id_action_note = '$id_action_note' ";
        } elseif (@$id_form != null || @$id_form != "") {
            $sql .= " WHERE id_form = '$id_form' ";
        } else {
            $sql .= " ORDER BY id_action_note ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function add_action_note(
        $id_action_note,
        $id_form,
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
            id_form = '$id_form',
            action_note_desc = '$action_note_desc',
            action_date_update = '$action_date_update',
            action_note_user = '$action_note_user'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_action_note(
        $id_action_note,
        $id_form,
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
            id_form = '$id_form',
            action_date_update = '$action_date_update',
            action_note_user = '$action_note_user'
            WHERE id_action_note = '$id_action_note'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_action_note(
        $id_action_note,
        $id_form,
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
        if (@$id_po != null || @$id_po != "") {
            $sql .= " WHERE id_po = '$id_po' ";
        } elseif (@$id_form_detail != null || @$id_form_detail != "") {
            $sql .= " WHERE id_form_detail = '$id_form_detail' ";
        } elseif (@$po_no != null || @$po_no != "") {
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
        if (@$id_so != null || @$id_so != "") {
            $sql .= " WHERE id_so = '$id_so' ";
        } elseif (@$id_form_detail != null || @$id_form_detail != "") {
            $sql .= " WHERE id_form_detail = '$id_form_detail' ";
        } elseif (@$so != null || @$so != "") {
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
        if (@$superior_id != null || @$superior_id != "") {
            $sql .= " WHERE superior_id = '$superior_id' ";
        } elseif (@$nama_superior != null || @$nama_superior != "") {
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

    function __destruct()
    {
        $db = $this->mysqli->conn;
        $db = $db->close();
    }
}
?>

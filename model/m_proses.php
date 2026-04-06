<?php
class DbTable
{
    protected $tb_user = "tb_users";
    protected $tb_action_note = "tb_action_note";
    protected $tb_form = "tb_form";
    protected $tb_form_detail = "tb_form_detail";

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
        $id_users = null,
        $username = null,
        $password = null,
        $nama_user = null,
        $foto = null,
        $id_tu = null,
        $no_telp = null,
        $token = null,
        $level = null,
        $status = null,
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
        $id_users = "",
        $username = null,
        $password = null,
        $nama_user = null,
        $foto = null,
        $id_tu = null,
        $no_telp = null,
        $token = null,
        $level = null,
        $status = null,
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
            status = '$status'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_user(
        $id_users = null,
        $username = null,
        $password = null,
        $nama_user = null,
        $foto = null,
        $id_tu = null,
        $no_telp = null,
        $token = null,
        $level = null,
        $status = null,
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
            status = '$status'
            WHERE id_users = '$id_users'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_user(
        $id_users = null,
        $username = null,
        $password = null,
        $nama_user = null,
        $foto = null,
        $id_tu = null,
        $no_telp = null,
        $token = null,
        $level = null,
        $status = null,
    ) {
        $db = $this->mysqli->conn;
        $table = $this->tb_user;
        $delete = $this->sql_delete;
        $sql = $delete;
        $sql .= $table;
        $sql .= " WHERE id_users = '$id_users'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    // ------------- TABEL FORM ----------------------------

    public function data_form(
        $id_from = null,
        $form_no = null,
        $form_serv_name = null,
        $form_check_by = null,
        $form_date_serv_name = null,
        $form_serv_comment = null,
        $form_superior_aprd = null,
        $form_superior_comment = null,
        $form_sadmin_comment = null,
        $form_shead_aprd = null,
        $form_shead_comment = null,
        $form_date_check_by = null,
        $from_date_update = null,
        $form_user_update = null,
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
            $sql .= " ORDER BY form_no ASC";
        }
        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function add_form(
        $id_from = null,
        $form_no = null,
        $form_serv_name = null,
        $form_check_by = null,
        $form_date_serv_name = null,
        $form_serv_comment = null,
        $form_superior_aprd = null,
        $form_superior_comment = null,
        $form_sadmin_comment = null,
        $form_shead_aprd = null,
        $form_shead_comment = null,
        $form_date_check_by = null,
        $from_date_update = null,
        $form_user_update = null,
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
            form_user_update = '$form_user_update'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_form(
        $id_from = null,
        $form_no = null,
        $form_serv_name = null,
        $form_check_by = null,
        $form_date_serv_name = null,
        $form_serv_comment = null,
        $form_superior_aprd = null,
        $form_superior_comment = null,
        $form_sadmin_comment = null,
        $form_shead_aprd = null,
        $form_shead_comment = null,
        $form_date_check_by = null,
        $from_date_update = null,
        $form_user_update = null,
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
            form_user_update = '$form_user_update'
            WHERE id_from = '$id_from'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_form(
        $id_from = null,
        $form_no = null,
        $form_serv_name = null,
        $form_check_by = null,
        $form_date_serv_name = null,
        $form_serv_comment = null,
        $form_superior_aprd = null,
        $form_superior_comment = null,
        $form_sadmin_comment = null,
        $form_shead_aprd = null,
        $form_shead_comment = null,
        $form_date_check_by = null,
        $from_date_update = null,
        $form_user_update = null,
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
        $id_form_detail = null,
        $id_form = null,
        $form_comment = null,
        $pn_group = null,
        $pn_desc = null,
        $qty = null,
        $explan = null,
        $action_note = null,
        $form_detail_date = null,
        $form_detail_user = null,
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
        $id_form_detail = null,
        $id_form = null,
        $form_comment = null,
        $pn_group = null,
        $pn_desc = null,
        $qty = null,
        $explan = null,
        $action_note = null,
        $form_detail_date = null,
        $form_detail_user = null,
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
            form_detail_date = '$form_detail_date',
            form_detail_user = '$form_detail_user'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function edit_form_form(
        $id_form_detail = null,
        $id_form = null,
        $form_comment = null,
        $pn_group = null,
        $pn_desc = null,
        $qty = null,
        $explan = null,
        $action_note = null,
        $form_detail_date = null,
        $form_detail_user = null,
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
            form_detail_date = '$form_detail_date',
            form_detail_user = '$form_detail_user'
            WHERE id_form_detail = '$id_form_detail'
            ";

        ($query = $db->query($sql)) or die($db->error);
        return $query;
    }

    public function delete_form_detail(
        $id_form_detail = null,
        $id_form = null,
        $form_comment = null,
        $pn_group = null,
        $pn_desc = null,
        $qty = null,
        $explan = null,
        $action_note = null,
        $form_detail_date = null,
        $form_detail_user = null,
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
        $id_action_note = null,
        $id_form = null,
        $action_note_desc = null,
        $action_date_update = null,
        $action_note_user = null,
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
        $id_action_note = null,
        $id_form = null,
        $action_note_desc = null,
        $action_date_update = null,
        $action_note_user = null,
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
        $id_action_note = null,
        $id_form = null,
        $action_note_desc = null,
        $action_date_update = null,
        $action_note_user = null,
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
        $id_action_note = null,
        $id_form = null,
        $action_note_desc = null,
        $action_date_update = null,
        $action_note_user = null,
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

    function __destruct()
    {
        $db = $this->mysqli->conn;
        $db = $db->close();
    }
}
?>

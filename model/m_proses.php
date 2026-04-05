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

    function __destruct()
    {
        $db = $this->mysqli->conn;
        $db = $db->close();
    }
}
?>

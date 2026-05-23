<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Akses data tb_users (login, auth token, CRUD, upload).
 */
class UserRepository extends RepositoryBase
{
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

    /**
     * @param array<int, mixed> $params
     */
    private function user_list_append_level_filter(
        string &$sql,
        string &$types,
        array &$params,
        bool &$has_where,
        $level,
    ): void {
        $level_trim = trim((string) ($level ?? ""));
        if ($level_trim === "") {
            return;
        }
        if ($has_where) {
            $sql .= " AND u.level = ? ";
        } else {
            $sql .= " WHERE u.level = ? ";
            $has_where = true;
        }
        $types .= "s";
        $params[] = $level_trim;
    }

    private function user_list_order_limit_suffix($limit, $offset): string
    {
        $suffix = " ORDER BY u.nama_user ASC";
        if ($limit !== null && (int) $limit > 0) {
            $suffix .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }
        return $suffix;
    }

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
            $sql .=
                " WHERE (u.username LIKE ? OR u.nama_user LIKE ? OR u.no_telp LIKE ? OR u.level LIKE ? OR IFNULL(u.status,'') LIKE ? OR CAST(u.id_users AS CHAR) LIKE ? OR IFNULL(s.nama_superior,'') LIKE ?) ";
            $types = "sssssss";
            $params = [$like, $like, $like, $like, $like, $like, $like];
            $has_where = true;
            $this->user_list_append_level_filter($sql, $types, $params, $has_where, $level);
            $sql .= $this->user_list_order_limit_suffix($limit, $offset);
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
            $params = [];
            $types = "";
            $has_where = false;
            $this->user_list_append_level_filter($sql, $types, $params, $has_where, $level);
            $sql .= $this->user_list_order_limit_suffix($limit, $offset);
            if ($has_where) {
                return $this->db_run_select($sql, $types, $params);
            }
            return $this->db_query($sql);
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
        $level = null,
    ) {
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
            $sql .=
                " WHERE (u.username LIKE ? OR u.nama_user LIKE ? OR u.no_telp LIKE ? OR u.level LIKE ? OR IFNULL(u.status,'') LIKE ? OR CAST(u.id_users AS CHAR) LIKE ? OR IFNULL(s.nama_superior,'') LIKE ?) ";
            $types = "sssssss";
            $params = [$like, $like, $like, $like, $like, $like, $like];
            $has_where = true;
            $this->user_list_append_level_filter($sql, $types, $params, $has_where, $level);
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
            $params = [];
            $types = "";
            $has_where = false;
            $this->user_list_append_level_filter($sql, $types, $params, $has_where, $level);
            if ($has_where) {
                return $this->db_run_select($sql, $types, $params);
            }
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
}

<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository superior.
 */
class SuperiorRepository extends RepositoryBase
{
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
}

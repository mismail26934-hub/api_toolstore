<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository superior.
 */
class SuperiorRepository extends RepositoryBase
{
    /**
     * WHERE untuk list / count superior (filter eksak atau keyword search).
     */
    private function superior_list_where_sql(
        $db,
        $superior_id,
        $nama_superior,
        $status_superior,
        $search,
        string $alias = "s",
    ): string {
        $a = $alias;
        if (trim((string) ($superior_id ?? "")) !== "") {
            $id_esc = $db->real_escape_string((string) $superior_id);
            return " WHERE $a.superior_id = '$id_esc' ";
        }
        if (trim((string) ($nama_superior ?? "")) !== "") {
            $name_esc = $db->real_escape_string((string) $nama_superior);
            return " WHERE $a.nama_superior = '$name_esc' ";
        }

        $search_trim = trim((string) ($search ?? ""));
        if ($search_trim === "") {
            if (trim((string) ($status_superior ?? "")) !== "") {
                $status_esc = $db->real_escape_string(
                    (string) $status_superior,
                );
                return " WHERE $a.status_superior = '$status_esc' ";
            }
            return "";
        }

        $escaped = $db->real_escape_string($search_trim);
        $like = "'%$escaped%'";
        $sql =
            " WHERE ($a.nama_superior LIKE $like OR CAST($a.superior_id AS CHAR) LIKE $like " .
            "OR IFNULL($a.status_superior,'') LIKE $like) ";
        if (trim((string) ($status_superior ?? "")) !== "") {
            $status_esc = $db->real_escape_string((string) $status_superior);
            $sql .= " AND $a.status_superior = '$status_esc' ";
        }

        return $sql;
    }

    /**
     * List superior untuk VIEW (satu WHERE; skip list jika search aktif & total 0).
     *
     * @return array{total: int, rows: list<object>}
     */
    public function superior_list_view(
        $superior_id,
        $nama_superior,
        $status_superior,
        $search = null,
        $limit = null,
        $offset = 0,
    ): array {
        $db = $this->mysqli->conn;
        $table = $this->tb_superior;
        $where = $this->superior_list_where_sql(
            $db,
            $superior_id,
            $nama_superior,
            $status_superior,
            $search,
        );

        $countSql = "SELECT COUNT(*) AS cnt FROM $table s " . $where;
        $countResult = $this->db_query($countSql);
        $total = 0;
        if ($countResult instanceof mysqli_result) {
            $row = $countResult->fetch_object();
            if ($row !== null && isset($row->cnt)) {
                $total = (int) $row->cnt;
            }
            $countResult->free();
        }

        $search_trim = trim((string) ($search ?? ""));
        $has_exact_filter =
            trim((string) ($superior_id ?? "")) !== "" ||
            trim((string) ($nama_superior ?? "")) !== "";
        $rows = [];
        if ($total > 0 || $search_trim === "") {
            $listSql =
                $this->sql_select .
                $table .
                " s " .
                $where .
                " ORDER BY s.nama_superior ASC";
            if (
                !$has_exact_filter &&
                $limit !== null &&
                (int) $limit > 0
            ) {
                $listSql .=
                    " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
            }
            $listResult = $this->db_query($listSql);
            if ($listResult instanceof mysqli_result) {
                while ($row = $listResult->fetch_object()) {
                    if ($row !== null) {
                        $rows[] = $row;
                    }
                }
                $listResult->free();
            }
        }

        return ["total" => $total, "rows" => $rows];
    }

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

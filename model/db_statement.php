<?php

/**
 * Prepared statement helpers for Proses_sql.
 */
trait DbStatementTrait
{
    private function db(): mysqli
    {
        return $this->mysqli->conn;
    }

    /**
     * @return mysqli_result|bool
     */
    private function db_query(string $sql)
    {
        $db = $this->db();
        $result = $db->query($sql);
        if ($result === false) {
            sql_fail($db);
        }
        return $result;
    }

    /**
     * @param array<string, string|null> $data
     */
    private function db_insert(string $table, array $data): mysqli_stmt
    {
        $db = $this->db();
        $cols = array_keys($data);
        $vals = array_map(
            static fn($v) => $v === null ? "" : (string) $v,
            array_values($data),
        );
        $placeholders = implode(", ", array_fill(0, count($cols), "?"));
        $colList = implode(", ", $cols);
        $sql = "INSERT INTO $table ($colList) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            sql_fail($db);
        }
        $types = str_repeat("s", count($vals));
        $stmt->bind_param($types, ...$vals);
        if (!$stmt->execute()) {
            sql_fail($db);
        }
        return $stmt;
    }

    /**
     * @param array<string, string|null> $data
     */
    private function db_update(
        string $table,
        array $data,
        string $whereCol,
        string $whereVal,
    ): mysqli_stmt {
        $db = $this->db();
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = "$col = ?";
        }
        $vals = array_map(
            static fn($v) => $v === null ? "" : (string) $v,
            array_values($data),
        );
        $vals[] = (string) $whereVal;
        $sql =
            "UPDATE $table SET " .
            implode(", ", $sets) .
            " WHERE $whereCol = ?";
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            sql_fail($db);
        }
        $types = str_repeat("s", count($vals));
        $stmt->bind_param($types, ...$vals);
        if (!$stmt->execute()) {
            sql_fail($db);
        }
        return $stmt;
    }

    private function db_delete_where(
        string $table,
        string $whereCol,
        string $whereVal,
    ): mysqli_stmt {
        $db = $this->db();
        $sql = "DELETE FROM $table WHERE $whereCol = ?";
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            sql_fail($db);
        }
        $whereVal = (string) $whereVal;
        $stmt->bind_param("s", $whereVal);
        if (!$stmt->execute()) {
            sql_fail($db);
        }
        return $stmt;
    }

    /**
     * @return mysqli_result|false
     */
    private function db_select_where(
        string $table,
        string $whereCol,
        string $whereVal,
        ?string $orderBy = null,
    ) {
        $db = $this->db();
        $sql = "SELECT * FROM $table WHERE $whereCol = ?";
        if ($orderBy !== null && $orderBy !== "") {
            $sql .= " ORDER BY $orderBy";
        }
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            sql_fail($db);
        }
        $whereVal = (string) $whereVal;
        $stmt->bind_param("s", $whereVal);
        if (!$stmt->execute()) {
            sql_fail($db);
        }
        return $stmt->get_result();
    }

    /**
     * @return mysqli_result|false
     */
    private function db_run_select(string $sql, string $types, array $params)
    {
        $db = $this->db();
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            sql_fail($db);
        }
        if ($types !== "") {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            sql_fail($db);
        }
        return $stmt->get_result();
    }
}

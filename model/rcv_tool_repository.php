<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository rcv tool.
 */
class RcvToolRepository extends RepositoryBase
{
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
            $this->actionByValue($rcv_tool_id_input),
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
            $this->actionByValue($rcv_tool_id_input),
        );
    }

    /**
     * id_form_detail yang sudah punya record receive Tool Store untuk form tertentu.
     *
     * @return list<string>
     */
    public function form_detail_ids_by_form(string $id_form): array
    {
        $id_form = trim($id_form);
        if ($id_form === "") {
            return [];
        }

        $rt = $this->tb_rcv_tool;
        $fd = $this->tb_form_detail;
        $sql =
            "SELECT DISTINCT rt.id_form_detail FROM $rt rt " .
            "INNER JOIN $fd fd ON fd.id_form_detail = rt.id_form_detail " .
            "WHERE fd.id_form = ?";

        $result = $this->db_run_select($sql, "s", [$id_form]);
        if (!($result instanceof mysqli_result)) {
            return [];
        }

        $ids = [];
        while ($row = $result->fetch_object()) {
            $id = trim((string) ($row->id_form_detail ?? ""));
            if ($id !== "") {
                $ids[] = $id;
            }
        }
        $result->free();

        return $ids;
    }

    /**
     * Semua rcv_tool_date per id_form_detail untuk form (urut id_rcv_tool, tanpa DISTINCT).
     *
     * @return array<string, list<string>>
     */
    public function rcv_tool_dates_by_form(string $id_form): array
    {
        $id_form = trim($id_form);
        if ($id_form === "") {
            return [];
        }

        $rt = $this->tb_rcv_tool;
        $fd = $this->tb_form_detail;
        $sql =
            "SELECT rt.id_form_detail, rt.rcv_tool_date FROM $rt rt " .
            "INNER JOIN $fd fd ON fd.id_form_detail = rt.id_form_detail " .
            "WHERE fd.id_form = ? " .
            "ORDER BY rt.id_form_detail ASC, rt.id_rcv_tool ASC";

        $result = $this->db_run_select($sql, "s", [$id_form]);
        if (!($result instanceof mysqli_result)) {
            return [];
        }

        $dates = [];
        while ($row = $result->fetch_object()) {
            $id = trim((string) ($row->id_form_detail ?? ""));
            $date = trim((string) ($row->rcv_tool_date ?? ""));
            if ($id === "" || $date === "") {
                continue;
            }
            $dates[$id][] = $date;
        }
        $result->free();

        return $dates;
    }
}

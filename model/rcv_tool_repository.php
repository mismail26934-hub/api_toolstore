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
}

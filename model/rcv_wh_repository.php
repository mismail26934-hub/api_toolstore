<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository rcv wh.
 */
class RcvWhRepository extends RepositoryBase
{
    public function data_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        if ((@$id_rcv_wh ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_rcv_wh,
                "id_rcv_wh",
                $id_rcv_wh,
            );
        }
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_rcv_wh,
                "id_form_detail",
                $id_form_detail,
                "id_form_detail ASC",
            );
        }
        return $this->db_query(
            $this->sql_select .
                $this->tb_rcv_wh .
                " ORDER BY id_form_detail ASC",
        );
    }

    public function add_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->db_insert($this->tb_rcv_wh, [
            "id_rcv_wh" => $id_rcv_wh,
            "id_form_detail" => $id_form_detail,
            "rcv_wh_date" => $rcv_wh_date,
            "rcv_wh_id_input" => $rcv_wh_id_input,
            "rcv_wh_date_input" => $rcv_wh_date_input,
        ]);
    }

    public function edit_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->db_update(
            $this->tb_rcv_wh,
            [
                "id_form_detail" => $id_form_detail,
                "rcv_wh_date" => $rcv_wh_date,
                "rcv_wh_id_input" => $rcv_wh_id_input,
                "rcv_wh_date_input" => $rcv_wh_date_input,
            ],
            "id_rcv_wh",
            $id_rcv_wh,
        );
    }

    public function delete_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->db_delete_where(
            $this->tb_rcv_wh,
            "id_rcv_wh",
            $id_rcv_wh,
        );
    }
}

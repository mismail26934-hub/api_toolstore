<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository po.
 */
class PoRepository extends RepositoryBase
{
    public function data_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        if ((@$id_po ?? "") !== "") {
            return $this->db_select_where($this->tb_po, "id_po", $id_po);
        }
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_po,
                "id_form_detail",
                $id_form_detail,
                "id_form_detail ASC",
            );
        }
        if ((@$po_no ?? "") !== "") {
            return $this->db_select_where($this->tb_po, "po_no", $po_no);
        }
        return $this->db_query(
            $this->sql_select . $this->tb_po . " ORDER BY id_form_detail ASC",
        );
    }

    public function add_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->db_insert($this->tb_po, [
            "id_po" => $id_po,
            "id_form_detail" => $id_form_detail,
            "po_no" => $po_no,
            "date_update_po" => $date_update_po,
            "user_update_po" => $user_update_po,
        ]);
    }

    public function edit_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->db_update(
            $this->tb_po,
            [
                "id_form_detail" => $id_form_detail,
                "po_no" => $po_no,
                "date_update_po" => $date_update_po,
                "user_update_po" => $user_update_po,
            ],
            "id_po",
            $id_po,
        );
    }

    public function delete_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->db_delete_where($this->tb_po, "id_po", $id_po);
    }
}

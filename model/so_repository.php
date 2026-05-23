<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository so.
 */
class SoRepository extends RepositoryBase
{
    public function data_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        if ((@$id_so ?? "") !== "") {
            return $this->db_select_where($this->tb_so, "id_so", $id_so);
        }
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_so,
                "id_form_detail",
                $id_form_detail,
                "id_form_detail ASC",
            );
        }
        if ((@$so ?? "") !== "") {
            return $this->db_select_where($this->tb_so, "so", $so);
        }
        return $this->db_query(
            $this->sql_select . $this->tb_so . " ORDER BY id_form_detail ASC",
        );
    }

    public function add_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        return $this->db_insert($this->tb_so, [
            "id_so" => $id_so,
            "id_form_detail" => $id_form_detail,
            "so" => $so,
            "eta" => $eta,
            "note_so" => $note_so,
            "date_update_so" => $date_update_so,
            "id_update_so" => $id_update_so,
        ]);
    }

    public function edit_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        return $this->db_update(
            $this->tb_so,
            [
                "id_form_detail" => $id_form_detail,
                "so" => $so,
                "eta" => $eta,
                "note_so" => $note_so,
                "date_update_so" => $date_update_so,
                "id_update_so" => $id_update_so,
            ],
            "id_so",
            $id_so,
        );
    }

    public function delete_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        return $this->db_delete_where($this->tb_so, "id_so", $id_so);
    }
}

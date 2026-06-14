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
        return $this->db_update_if_changed(
            $this->tb_po,
            [
                "id_form_detail" => $id_form_detail,
                "po_no" => $po_no,
                "date_update_po" => $date_update_po,
                "user_update_po" => $user_update_po,
            ],
            "id_po",
            $id_po,
            $this->actionByValue($user_update_po),
        );
    }

    public function delete_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->db_delete_where(
            $this->tb_po,
            "id_po",
            $id_po,
            $this->actionByValue($user_update_po),
        );
    }

    /**
     * Semua po_no per id_form_detail untuk form (urut id_po, tanpa DISTINCT).
     *
     * @return array<string, list<string>>
     */
    public function po_nos_by_form(string $id_form): array
    {
        $id_form = trim($id_form);
        if ($id_form === "") {
            return [];
        }

        $po = $this->tb_po;
        $fd = $this->tb_form_detail;
        $sql =
            "SELECT p.id_form_detail, p.po_no FROM $po p " .
            "INNER JOIN $fd fd ON fd.id_form_detail = p.id_form_detail " .
            "WHERE fd.id_form = ? " .
            "ORDER BY p.id_form_detail ASC, p.id_po ASC";

        $result = $this->db_run_select($sql, "s", [$id_form]);
        if (!($result instanceof mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $result->fetch_object()) {
            $id = trim((string) ($row->id_form_detail ?? ""));
            $poNo = trim((string) ($row->po_no ?? ""));
            if ($id === "" || $poNo === "") {
                continue;
            }
            $rows[$id][] = $poNo;
        }
        $result->free();

        return $rows;
    }
}

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

    /**
     * Semua entri SO per id_form_detail untuk form (urut id_so, tanpa DISTINCT).
     *
     * @return array<string, list<array{so: string, eta: string, note_so: string}>>
     */
    public function so_entries_by_form(string $id_form): array
    {
        $id_form = trim($id_form);
        if ($id_form === "") {
            return [];
        }

        $so = $this->tb_so;
        $fd = $this->tb_form_detail;
        $sql =
            "SELECT s.id_form_detail, s.so, s.eta, s.note_so FROM $so s " .
            "INNER JOIN $fd fd ON fd.id_form_detail = s.id_form_detail " .
            "WHERE fd.id_form = ? " .
            "ORDER BY s.id_form_detail ASC, s.id_so ASC";

        $result = $this->db_run_select($sql, "s", [$id_form]);
        if (!($result instanceof mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $result->fetch_object()) {
            $id = trim((string) ($row->id_form_detail ?? ""));
            $soNo = trim((string) ($row->so ?? ""));
            if ($id === "" || $soNo === "") {
                continue;
            }
            $rows[$id][] = [
                "so" => $soNo,
                "eta" => trim((string) ($row->eta ?? "")),
                "note_so" => trim((string) ($row->note_so ?? "")),
            ];
        }
        $result->free();

        return $rows;
    }
}

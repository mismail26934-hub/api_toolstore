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
        return $this->db_update_if_changed(
            $this->tb_rcv_wh,
            [
                "id_form_detail" => $id_form_detail,
                "rcv_wh_date" => $rcv_wh_date,
                "rcv_wh_id_input" => $rcv_wh_id_input,
                "rcv_wh_date_input" => $rcv_wh_date_input,
            ],
            "id_rcv_wh",
            $id_rcv_wh,
            $this->actionByValue($rcv_wh_id_input),
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
            $this->actionByValue($rcv_wh_id_input),
        );
    }

    /**
     * id_form_detail yang sudah punya record receive WH/GA untuk form tertentu.
     *
     * @return list<string>
     */
    public function form_detail_ids_by_form(string $id_form): array
    {
        $id_form = trim($id_form);
        if ($id_form === "") {
            return [];
        }

        $rw = $this->tb_rcv_wh;
        $fd = $this->tb_form_detail;
        $sql =
            "SELECT DISTINCT rw.id_form_detail FROM $rw rw " .
            "INNER JOIN $fd fd ON fd.id_form_detail = rw.id_form_detail " .
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
     * Semua rcv_wh_date per id_form_detail untuk form (urut id_rcv_wh, tanpa DISTINCT).
     *
     * @return array<string, list<string>>
     */
    public function rcv_wh_dates_by_form(string $id_form): array
    {
        $id_form = trim($id_form);
        if ($id_form === "") {
            return [];
        }

        $rw = $this->tb_rcv_wh;
        $fd = $this->tb_form_detail;
        $sql =
            "SELECT rw.id_form_detail, rw.rcv_wh_date FROM $rw rw " .
            "INNER JOIN $fd fd ON fd.id_form_detail = rw.id_form_detail " .
            "WHERE fd.id_form = ? " .
            "ORDER BY rw.id_form_detail ASC, rw.id_rcv_wh ASC";

        $result = $this->db_run_select($sql, "s", [$id_form]);
        if (!($result instanceof mysqli_result)) {
            return [];
        }

        $dates = [];
        while ($row = $result->fetch_object()) {
            $id = trim((string) ($row->id_form_detail ?? ""));
            $date = trim((string) ($row->rcv_wh_date ?? ""));
            if ($id === "" || $date === "") {
                continue;
            }
            $dates[$id][] = $date;
        }
        $result->free();

        return $dates;
    }
}

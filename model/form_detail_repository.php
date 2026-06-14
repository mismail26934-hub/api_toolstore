<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository form detail.
 */
class FormDetailRepository extends RepositoryBase
{
    public function data_form_detail(
        $id_form_detail,
        $id_form,
        $form_comment,
        $pn_group,
        $pn_desc,
        $qty,
        $explan,
        $action_note,
        $val_type,
        $part_value,
        $form_detail_milestone,
        $form_detail_date,
        $form_detail_user,
    ) {
        if ((@$id_form_detail ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_form_detail,
                "id_form_detail",
                $id_form_detail,
            );
        }
        if ((@$id_form ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_form_detail,
                "id_form",
                $id_form,
                "id_form ASC",
            );
        }
        return $this->db_query(
            $this->sql_select . $this->tb_form_detail . " ORDER BY id_form ASC",
        );
    }

    public function add_form_detail(
        $id_form_detail,
        $id_form,
        $form_comment,
        $pn_group,
        $pn_desc,
        $qty,
        $explan,
        $action_note,
        $val_type,
        $part_value,
        $form_detail_milestone,
        $form_detail_date,
        $form_detail_user,
    ) {
        return $this->db_insert($this->tb_form_detail, [
            "id_form_detail" => $id_form_detail,
            "id_form" => $id_form,
            "form_comment" => $form_comment,
            "pn_group" => $pn_group,
            "pn_desc" => $pn_desc,
            "qty" => $qty,
            "explan" => $explan,
            "action_note" => $action_note,
            "val_type" => $val_type,
            "part_value" => $part_value,
            "form_detail_milestone" => $form_detail_milestone,
            "form_detail_date" => $form_detail_date,
            "form_detail_user" => $form_detail_user,
        ]);
    }

    public function edit_form_detail(
        $id_form_detail,
        $id_form,
        $form_comment,
        $pn_group,
        $pn_desc,
        $qty,
        $explan,
        $action_note,
        $val_type,
        $part_value,
        $form_detail_milestone,
        $form_detail_date,
        $form_detail_user,
    ) {
        return $this->db_update(
            $this->tb_form_detail,
            [
                "id_form" => $id_form,
                "form_comment" => $form_comment,
                "pn_group" => $pn_group,
                "pn_desc" => $pn_desc,
                "qty" => $qty,
                "explan" => $explan,
                "action_note" => $action_note,
                "val_type" => $val_type,
                "part_value" => $part_value,
                "form_detail_milestone" => $form_detail_milestone,
                "form_detail_date" => $form_detail_date,
                "form_detail_user" => $form_detail_user,
            ],
            "id_form_detail",
            $id_form_detail,
            $this->actionByValue($form_detail_user),
        );
    }

    public function delete_form_detail(
        $id_form_detail,
        $id_form,
        $form_comment,
        $pn_group,
        $pn_desc,
        $qty,
        $explan,
        $action_note,
        $val_type,
        $part_value,
        $form_detail_milestone,
        $form_detail_date,
        $form_detail_user,
    ) {
        return $this->db_delete_where(
            $this->tb_form_detail,
            "id_form_detail",
            $id_form_detail,
            $this->actionByValue($form_detail_user),
        );
    }

    /**
     * Filter rentang tanggal kolom from_date_update (export).
     * Format YYYY-MM-DD; kosong = tidak difilter.
     */
    private function export_date_filter_sql(
        mysqli $db,
        ?string $from_date_update,
        ?string $to_date_update,
        string $formAlias = "f",
    ): string {
        $from = trim((string) ($from_date_update ?? ""));
        if ($from === "") {
            return "";
        }

        $to = trim((string) ($to_date_update ?? ""));
        if ($to === "") {
            $to = $from;
        }

        if (
            !preg_match("/^\d{4}-\d{2}-\d{2}$/", $from) ||
            !preg_match("/^\d{4}-\d{2}-\d{2}$/", $to)
        ) {
            return "";
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $from_esc = $db->real_escape_string($from);
        $to_esc = $db->real_escape_string($to);

        return " AND DATE($formAlias.from_date_update) >= '$from_esc' AND DATE($formAlias.from_date_update) <= '$to_esc' ";
    }

    /**
     * Export form detail dengan join form, user, superior, PO, SO, RCV.
     *
     * @return mysqli_result|false
     */
    public function export_form_detail_full(
        ?string $id_form = null,
        ?string $from_date_update = null,
        ?string $to_date_update = null,
    ) {
        $fd = $this->tb_form_detail;
        $f = $this->tb_form;
        $u = $this->tb_user;
        $sup = $this->tb_superior;
        $po = $this->tb_po;
        $so = $this->tb_so;
        $rw = $this->tb_rcv_wh;
        $rt = $this->tb_rcv_tool;

        $sql =
            "SELECT " .
            "fd.id_form_detail, fd.id_form, fd.form_comment, fd.pn_group, fd.pn_desc, " .
            "fd.qty, fd.explan, fd.action_note, fd.val_type, fd.part_value, " .
            "fd.form_detail_milestone, fd.form_detail_date, fd.form_detail_user, " .
            "f.form_no, f.form_serv_name, f.form_check_by, f.form_date_serv_name, " .
            "f.form_serv_comment, f.form_superior_aprd, f.form_superior_comment, " .
            "f.form_sadmin_comment, f.form_shead_aprd, f.form_shead_comment, " .
            "f.form_date_check_by, f.from_date_update, f.form_user_update, " .
            "f.form_date_superior_aprd, f.form_date_sadmin_comment, f.form_date_shead_aprd, " .
            "f.form_milestone, f.form_status_order, " .
            "u_form.nama_user AS form_update_nama_user, " .
            "u_form.username AS form_update_username, " .
            "u_form.superior_id AS form_update_superior_id, " .
            "sup_form.nama_superior AS form_update_nama_superior, " .
            "u_detail.nama_user AS detail_nama_user, " .
            "u_detail.username AS detail_username, " .
            "u_detail.superior_id AS detail_superior_id, " .
            "sup_detail.nama_superior AS detail_nama_superior, " .
            "GROUP_CONCAT(DISTINCT p.po_no ORDER BY p.id_po SEPARATOR ' | ') AS po_no_list, " .
            "GROUP_CONCAT(DISTINCT p.date_update_po ORDER BY p.id_po SEPARATOR ' | ') AS po_date_list, " .
            "GROUP_CONCAT(DISTINCT s.so ORDER BY s.id_so SEPARATOR ' | ') AS so_list, " .
            "GROUP_CONCAT(DISTINCT s.eta ORDER BY s.id_so SEPARATOR ' | ') AS so_eta_list, " .
            "GROUP_CONCAT(DISTINCT s.note_so ORDER BY s.id_so SEPARATOR ' | ') AS so_note_list, " .
            "GROUP_CONCAT(DISTINCT s.date_update_so ORDER BY s.id_so SEPARATOR ' | ') AS so_date_list, " .
            "GROUP_CONCAT(DISTINCT rw.rcv_wh_date ORDER BY rw.id_rcv_wh SEPARATOR ' | ') AS rcv_wh_date_list, " .
            "GROUP_CONCAT(DISTINCT rt.rcv_tool_date ORDER BY rt.id_rcv_tool SEPARATOR ' | ') AS rcv_tool_date_list " .
            "FROM $fd fd " .
            "INNER JOIN $f f ON f.id_form = fd.id_form " .
            "LEFT JOIN $u u_form ON u_form.id_users = f.form_user_update " .
            "LEFT JOIN $sup sup_form ON sup_form.superior_id = u_form.superior_id " .
            "LEFT JOIN $u u_detail ON u_detail.id_users = fd.form_detail_user " .
            "LEFT JOIN $sup sup_detail ON sup_detail.superior_id = u_detail.superior_id " .
            "LEFT JOIN $po p ON p.id_form_detail = fd.id_form_detail " .
            "LEFT JOIN $so s ON s.id_form_detail = fd.id_form_detail " .
            "LEFT JOIN $rw rw ON rw.id_form_detail = fd.id_form_detail " .
            "LEFT JOIN $rt rt ON rt.id_form_detail = fd.id_form_detail ";

        $db = $this->db();
        $where = "";
        $params = [];
        $types = "";

        $id_form_trim = trim((string) ($id_form ?? ""));
        if ($id_form_trim !== "") {
            $where = "WHERE fd.id_form = ?";
            $params[] = $id_form_trim;
            $types = "s";
        }

        $dateSql = $this->export_date_filter_sql(
            $db,
            $from_date_update,
            $to_date_update,
        );
        if ($dateSql !== "") {
            if ($where === "") {
                $where = "WHERE 1=1" . $dateSql;
            } else {
                $where .= $dateSql;
            }
        }

        $sql .= $where !== "" ? $where . " " : "";
        $sql .= "GROUP BY fd.id_form_detail ";
        $sql .= "ORDER BY f.form_no ASC, fd.id_form_detail ASC";

        if ($types !== "") {
            return $this->db_run_select($sql, $types, $params);
        }

        return $this->db_query($sql);
    }
}

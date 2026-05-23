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
        );
    }
}

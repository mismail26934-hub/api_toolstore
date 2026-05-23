<?php

require_once __DIR__ . "/repository_base.php";

/**
 * Repository action note.
 */
class ActionNoteRepository extends RepositoryBase
{
    public function data_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        if ((@$id_action_note ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_action_note,
                "id_action_note",
                $id_action_note,
            );
        }
        if ((@$action_note_desc ?? "") !== "") {
            return $this->db_select_where(
                $this->tb_action_note,
                "action_note_desc",
                $action_note_desc,
            );
        }
        return $this->db_query(
            $this->sql_select .
                $this->tb_action_note .
                " ORDER BY id_action_note ASC",
        );
    }

    public function add_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->db_insert($this->tb_action_note, [
            "id_action_note" => $id_action_note,
            "note_initial" => $note_initial,
            "action_note_desc" => $action_note_desc,
            "action_date_update" => $action_date_update,
            "action_note_user" => $action_note_user,
        ]);
    }

    public function edit_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->db_update(
            $this->tb_action_note,
            [
                "action_note_desc" => $action_note_desc,
                "note_initial" => $note_initial,
                "action_date_update" => $action_date_update,
                "action_note_user" => $action_note_user,
            ],
            "id_action_note",
            $id_action_note,
        );
    }

    public function delete_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->db_delete_where(
            $this->tb_action_note,
            "id_action_note",
            $id_action_note,
        );
    }
}

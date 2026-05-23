<?php

require_once __DIR__ . "/../conn/api_bootstrap.php";
require_once __DIR__ . "/../conn/api_crud.php";

if (!api_is_post_with_param()) {
    return;
}

const NOTE_PARAM_ADD = "ADD DATA NOTE";
const NOTE_PARAM_EDIT = "EDIT DATA NOTE";
const NOTE_PARAM_VIEW = "VIEW DATA NOTE";
const NOTE_PARAM_DELETE = "DELETED DATA NOTE";

$result = [];
$data = api_bootstrap_data();
$param = api_post_param();

$id_form = $_POST["id_form"] ?? null;
$id_action_note = $_POST["id_action_note"] ?? null;
$action_note_desc = $_POST["action_note_desc"] ?? null;
$action_date_update = $_POST["action_date_update"] ?? null;
$action_note_user = $_POST["action_note_user"] ?? null;

$row_note_cek = null;
$id_action_note_cek = null;
$id_form_cek = null;
$action_note_desc_cek = null;

if (
    $param === NOTE_PARAM_ADD ||
    $param === NOTE_PARAM_EDIT ||
    $param === NOTE_PARAM_VIEW
) {
    $data_action_note = $data->data_action_note(
        $param === NOTE_PARAM_ADD || $param === NOTE_PARAM_EDIT
            ? ""
            : $id_action_note,
        $id_form,
        "",
        "",
        "",
    );

    if ($param === NOTE_PARAM_ADD || $param === NOTE_PARAM_EDIT) {
        $row_note_cek = $data_action_note->fetch_object();
        if ($row_note_cek !== null) {
            $id_action_note_cek = $row_note_cek->id_action_note;
            $id_form_cek = $row_note_cek->note_initial ?? null;
            $action_note_desc_cek = $row_note_cek->action_note_desc;
        }
    } elseif ($param === NOTE_PARAM_VIEW) {
        foreach (api_mysqli_fetch_all_objects($data_action_note) as $row_note) {
            $result[] = cont_action_note_format_row($row_note);
        }
    }
}

$response = cont_action_note_handle_mutation(
    $data,
    $param,
    $row_note_cek,
    $id_action_note,
    $id_form,
    $id_form_cek,
    $action_note_desc,
    $action_note_desc_cek,
    $action_date_update,
    $action_note_user,
);

api_crud_push_mutation(
    $result,
    $param,
    $response,
    NOTE_PARAM_ADD,
    NOTE_PARAM_EDIT,
    NOTE_PARAM_DELETE,
);

echo json_encode($result);

/**
 * @return array<string, mixed>
 */
function cont_action_note_format_row(?object $row): array
{
    if ($row === null) {
        return [
            "id_action_note" => "",
            "id_form" => "",
            "action_note_desc" => "",
            "action_date_update" => "",
            "action_note_user" => "",
        ];
    }

    return [
        "id_action_note" => $row->id_action_note,
        "id_form" => $row->note_initial ?? "",
        "action_note_desc" => $row->action_note_desc,
        "action_date_update" => $row->action_date_update,
        "action_note_user" => $row->action_note_user,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function cont_action_note_handle_mutation(
    Proses_sql $data,
    string $param,
    ?object $row_note_cek,
    $id_action_note,
    $id_form,
    $id_form_cek,
    $action_note_desc,
    $action_note_desc_cek,
    $action_date_update,
    $action_note_user,
): array {
    switch ($param) {
        case NOTE_PARAM_ADD:
            if ($row_note_cek !== null) {
                return api_crud_fail("DUPLICATE NOTE");
            }

            return api_crud_ok(
                $param,
                (bool) $data->add_action_note(
                    $id_action_note,
                    $id_form,
                    $action_note_desc,
                    $action_date_update,
                    $action_note_user,
                ),
                " FAILED",
            );

        case NOTE_PARAM_EDIT:
            if (
                $id_form != $id_form_cek &&
                $action_note_desc == $action_note_desc_cek
            ) {
                return api_crud_fail("DUPLICATE NOTE!");
            }

            if ($id_action_note === null || $id_action_note === "") {
                return api_crud_fail("ERROR $param !");
            }

            return api_crud_ok(
                $param,
                (bool) $data->edit_action_note(
                    $id_action_note,
                    $id_form,
                    $action_note_desc,
                    $action_date_update,
                    $action_note_user,
                ),
            );

        case NOTE_PARAM_DELETE:
            $delete_action_note = null;
            if ($id_action_note === null || $id_action_note === "") {
                $response = api_crud_fail("ERROR $param !");
            } else {
                $delete_action_note = $data->delete_action_note(
                    $id_action_note,
                    "",
                    "",
                    "",
                    "",
                );
            }

            if ($delete_action_note) {
                return api_crud_ok($param, true);
            }

            return api_crud_ok($param, false);

        default:
            return api_crud_unknown_param($param);
    }
}

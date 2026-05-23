<?php

require_once __DIR__ . "/../conn/env_loader.php";
require_once __DIR__ . "/db_table.php";
require_once __DIR__ . "/user_repository.php";
require_once __DIR__ . "/form_repository.php";
require_once __DIR__ . "/form_detail_repository.php";
require_once __DIR__ . "/action_note_repository.php";
require_once __DIR__ . "/po_repository.php";
require_once __DIR__ . "/so_repository.php";
require_once __DIR__ . "/superior_repository.php";
require_once __DIR__ . "/rcv_wh_repository.php";
require_once __DIR__ . "/rcv_tool_repository.php";

/**
 * Facade akses data — delegasi ke repository per domain.
 */
class Proses_sql extends DbTable
{
    private $mysqli;

    private ?UserRepository $userRepository = null;

    private ?FormRepository $formRepository = null;

    private ?FormDetailRepository $formDetailRepository = null;

    private ?ActionNoteRepository $actionNoteRepository = null;

    private ?PoRepository $poRepository = null;

    private ?SoRepository $soRepository = null;

    private ?SuperiorRepository $superiorRepository = null;

    private ?RcvWhRepository $rcvWhRepository = null;

    private ?RcvToolRepository $rcvToolRepository = null;

    public function __construct($conn)
    {
        $this->mysqli = $conn;
    }

    private function users(): UserRepository
    {
        if ($this->userRepository === null) {
            $this->userRepository = new UserRepository($this->mysqli);
        }

        return $this->userRepository;
    }

    private function forms(): FormRepository
    {
        if ($this->formRepository === null) {
            $this->formRepository = new FormRepository($this->mysqli);
        }

        return $this->formRepository;
    }

    private function formDetails(): FormDetailRepository
    {
        if ($this->formDetailRepository === null) {
            $this->formDetailRepository = new FormDetailRepository(
                $this->mysqli,
            );
        }

        return $this->formDetailRepository;
    }

    private function actionNotes(): ActionNoteRepository
    {
        if ($this->actionNoteRepository === null) {
            $this->actionNoteRepository = new ActionNoteRepository(
                $this->mysqli,
            );
        }

        return $this->actionNoteRepository;
    }

    private function pos(): PoRepository
    {
        if ($this->poRepository === null) {
            $this->poRepository = new PoRepository($this->mysqli);
        }

        return $this->poRepository;
    }

    private function sos(): SoRepository
    {
        if ($this->soRepository === null) {
            $this->soRepository = new SoRepository($this->mysqli);
        }

        return $this->soRepository;
    }

    private function superiors(): SuperiorRepository
    {
        if ($this->superiorRepository === null) {
            $this->superiorRepository = new SuperiorRepository($this->mysqli);
        }

        return $this->superiorRepository;
    }

    private function rcvWhs(): RcvWhRepository
    {
        if ($this->rcvWhRepository === null) {
            $this->rcvWhRepository = new RcvWhRepository($this->mysqli);
        }

        return $this->rcvWhRepository;
    }

    private function rcvTools(): RcvToolRepository
    {
        if ($this->rcvToolRepository === null) {
            $this->rcvToolRepository = new RcvToolRepository($this->mysqli);
        }

        return $this->rcvToolRepository;
    }

    // --- UserRepository ---

    public function find_user_by_username($username = null)
    {
        return $this->users()->find_user_by_username($username);
    }

    public function update_user_password($id_users, $passwordHash)
    {
        return $this->users()->update_user_password($id_users, $passwordHash);
    }

    public function verify_api_token($id_users, $token)
    {
        return $this->users()->verify_api_token($id_users, $token);
    }

    public function data_user(
        $id_users,
        $username,
        $password,
        $nama_user,
        $foto,
        $id_tu,
        $no_telp,
        $token,
        $level,
        $status,
        $superior_id,
        $limit = null,
        $offset = 0,
        $search = null,
    ) {
        return $this->users()->data_user(
            $id_users,
            $username,
            $password,
            $nama_user,
            $foto,
            $id_tu,
            $no_telp,
            $token,
            $level,
            $status,
            $superior_id,
            $limit,
            $offset,
            $search,
        );
    }

    public function count_user(
        $id_users,
        $username,
        $nama_user,
        $superior_id,
        $search = null,
        $level = null,
    ) {
        return $this->users()->count_user(
            $id_users,
            $username,
            $nama_user,
            $superior_id,
            $search,
            $level,
        );
    }

    public function add_user(
        $id_users,
        $username,
        $password,
        $nama_user,
        $foto,
        $id_tu,
        $no_telp,
        $token,
        $level,
        $status,
        $superior_id,
    ) {
        return $this->users()->add_user(
            $id_users,
            $username,
            $password,
            $nama_user,
            $foto,
            $id_tu,
            $no_telp,
            $token,
            $level,
            $status,
            $superior_id,
        );
    }

    public function edit_user(
        $id_users,
        $username,
        $password,
        $nama_user,
        $foto,
        $id_tu,
        $no_telp,
        $token,
        $level,
        $status,
        $superior_id,
    ) {
        return $this->users()->edit_user(
            $id_users,
            $username,
            $password,
            $nama_user,
            $foto,
            $id_tu,
            $no_telp,
            $token,
            $level,
            $status,
            $superior_id,
        );
    }

    public function delete_user(
        $id_users,
        $username,
        $password,
        $nama_user,
        $foto,
        $id_tu,
        $no_telp,
        $token,
        $level,
        $status,
        $superior_id,
    ) {
        return $this->users()->delete_user(
            $id_users,
            $username,
            $password,
            $nama_user,
            $foto,
            $id_tu,
            $no_telp,
            $token,
            $level,
            $status,
            $superior_id,
        );
    }

    public function get_next_user_id()
    {
        return $this->users()->get_next_user_id();
    }

    public function user_id_exists($id_users)
    {
        return $this->users()->user_id_exists($id_users);
    }

    public function username_exists($username, $exclude_id_users = "")
    {
        return $this->users()->username_exists($username, $exclude_id_users);
    }

    // --- FormRepository ---

    public function data_form(
        $id_form,
        $form_no,
        $form_serv_name,
        $form_check_by,
        $form_date_serv_name,
        $form_serv_comment,
        $form_superior_aprd,
        $form_superior_comment,
        $form_sadmin_comment,
        $form_shead_aprd,
        $form_shead_comment,
        $form_date_check_by,
        $from_date_update,
        $form_user_update,
        $form_date_superior_aprd,
        $form_date_sadmin_comment,
        $form_date_shead_aprd,
        $form_milestone,
        $form_status_order,
        $limit,
        $offset,
        $search = null,
        $search_field = "all",
    ) {
        return $this->forms()->data_form(
            $id_form,
            $form_no,
            $form_serv_name,
            $form_check_by,
            $form_date_serv_name,
            $form_serv_comment,
            $form_superior_aprd,
            $form_superior_comment,
            $form_sadmin_comment,
            $form_shead_aprd,
            $form_shead_comment,
            $form_date_check_by,
            $from_date_update,
            $form_user_update,
            $form_date_superior_aprd,
            $form_date_sadmin_comment,
            $form_date_shead_aprd,
            $form_milestone,
            $form_status_order,
            $limit,
            $offset,
            $search,
            $search_field,
        );
    }

    public function count_form(
        $id_form,
        $form_no,
        $form_serv_name,
        $search = null,
        $search_field = "all",
    ) {
        return $this->forms()->count_form(
            $id_form,
            $form_no,
            $form_serv_name,
            $search,
            $search_field,
        );
    }

    public function fetch_form_list_rows(
        $id_form,
        $form_no,
        $form_serv_name,
        $limit,
        $offset,
        $search = null,
        $search_field = "all",
    ) {
        return $this->forms()->fetch_form_list_rows(
            $id_form,
            $form_no,
            $form_serv_name,
            $limit,
            $offset,
            $search,
            $search_field,
        );
    }

    public function count_form_dashboard()
    {
        return $this->forms()->count_form_dashboard();
    }

    public function add_form(
        $id_form,
        $form_no,
        $form_serv_name,
        $form_serv_comment,
        $form_date_serv_name,
        $form_check_by,
        $form_date_check_by,
        $form_superior_aprd,
        $form_superior_comment,
        $form_date_superior_aprd,
        $form_sadmin_comment,
        $form_date_sadmin_comment,
        $form_shead_aprd,
        $form_shead_comment,
        $form_milestone,
        $form_status_order,
        $form_date_shead_aprd,
        $from_date_update,
        $form_user_update,
    ) {
        return $this->forms()->add_form(
            $id_form,
            $form_no,
            $form_serv_name,
            $form_serv_comment,
            $form_date_serv_name,
            $form_check_by,
            $form_date_check_by,
            $form_superior_aprd,
            $form_superior_comment,
            $form_date_superior_aprd,
            $form_sadmin_comment,
            $form_date_sadmin_comment,
            $form_shead_aprd,
            $form_shead_comment,
            $form_milestone,
            $form_status_order,
            $form_date_shead_aprd,
            $from_date_update,
            $form_user_update,
        );
    }

    public function edit_form(
        $id_form,
        $form_no,
        $form_serv_name,
        $form_serv_comment,
        $form_date_serv_name,
        $form_check_by,
        $form_date_check_by,
        $form_superior_aprd,
        $form_superior_comment,
        $form_date_superior_aprd,
        $form_sadmin_comment,
        $form_date_sadmin_comment,
        $form_shead_aprd,
        $form_shead_comment,
        $form_milestone,
        $form_status_order,
        $form_date_shead_aprd,
        $from_date_update,
        $form_user_update,
    ) {
        return $this->forms()->edit_form(
            $id_form,
            $form_no,
            $form_serv_name,
            $form_serv_comment,
            $form_date_serv_name,
            $form_check_by,
            $form_date_check_by,
            $form_superior_aprd,
            $form_superior_comment,
            $form_date_superior_aprd,
            $form_sadmin_comment,
            $form_date_sadmin_comment,
            $form_shead_aprd,
            $form_shead_comment,
            $form_milestone,
            $form_status_order,
            $form_date_shead_aprd,
            $from_date_update,
            $form_user_update,
        );
    }

    public function delete_form(
        $id_form,
        $form_no,
        $form_serv_name,
        $form_serv_comment,
        $form_date_serv_name,
        $form_check_by,
        $form_date_check_by,
        $form_superior_aprd,
        $form_superior_comment,
        $form_date_superior_aprd,
        $form_sadmin_comment,
        $form_date_sadmin_comment,
        $form_shead_aprd,
        $form_shead_comment,
        $form_milestone,
        $form_status_order,
        $form_date_shead_aprd,
        $from_date_update,
        $form_user_update,
    ) {
        return $this->forms()->delete_form(
            $id_form,
            $form_no,
            $form_serv_name,
            $form_serv_comment,
            $form_date_serv_name,
            $form_check_by,
            $form_date_check_by,
            $form_superior_aprd,
            $form_superior_comment,
            $form_date_superior_aprd,
            $form_sadmin_comment,
            $form_date_sadmin_comment,
            $form_shead_aprd,
            $form_shead_comment,
            $form_milestone,
            $form_status_order,
            $form_date_shead_aprd,
            $from_date_update,
            $form_user_update,
        );
    }

    // --- FormDetailRepository ---

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
        return $this->formDetails()->data_form_detail(
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
        return $this->formDetails()->add_form_detail(
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
        );
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
        return $this->formDetails()->edit_form_detail(
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
        return $this->formDetails()->delete_form_detail(
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
        );
    }

    // --- ActionNoteRepository ---

    public function data_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->actionNotes()->data_action_note(
            $id_action_note,
            $note_initial,
            $action_note_desc,
            $action_date_update,
            $action_note_user,
        );
    }

    public function add_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->actionNotes()->add_action_note(
            $id_action_note,
            $note_initial,
            $action_note_desc,
            $action_date_update,
            $action_note_user,
        );
    }

    public function edit_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->actionNotes()->edit_action_note(
            $id_action_note,
            $note_initial,
            $action_note_desc,
            $action_date_update,
            $action_note_user,
        );
    }

    public function delete_action_note(
        $id_action_note,
        $note_initial,
        $action_note_desc,
        $action_date_update,
        $action_note_user,
    ) {
        return $this->actionNotes()->delete_action_note(
            $id_action_note,
            $note_initial,
            $action_note_desc,
            $action_date_update,
            $action_note_user,
        );
    }

    // --- PoRepository ---

    public function data_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->pos()->data_po(
            $id_po,
            $id_form_detail,
            $po_no,
            $date_update_po,
            $user_update_po,
        );
    }

    public function add_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->pos()->add_po(
            $id_po,
            $id_form_detail,
            $po_no,
            $date_update_po,
            $user_update_po,
        );
    }

    public function edit_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->pos()->edit_po(
            $id_po,
            $id_form_detail,
            $po_no,
            $date_update_po,
            $user_update_po,
        );
    }

    public function delete_po(
        $id_po,
        $id_form_detail,
        $po_no,
        $date_update_po,
        $user_update_po,
    ) {
        return $this->pos()->delete_po(
            $id_po,
            $id_form_detail,
            $po_no,
            $date_update_po,
            $user_update_po,
        );
    }

    // --- SoRepository ---

    public function data_so(
        $id_so,
        $id_form_detail,
        $so,
        $eta,
        $note_so,
        $date_update_so,
        $id_update_so,
    ) {
        return $this->sos()->data_so(
            $id_so,
            $id_form_detail,
            $so,
            $eta,
            $note_so,
            $date_update_so,
            $id_update_so,
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
        return $this->sos()->add_so(
            $id_so,
            $id_form_detail,
            $so,
            $eta,
            $note_so,
            $date_update_so,
            $id_update_so,
        );
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
        return $this->sos()->edit_so(
            $id_so,
            $id_form_detail,
            $so,
            $eta,
            $note_so,
            $date_update_so,
            $id_update_so,
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
        return $this->sos()->delete_so(
            $id_so,
            $id_form_detail,
            $so,
            $eta,
            $note_so,
            $date_update_so,
            $id_update_so,
        );
    }

    // --- SuperiorRepository ---

    public function data_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        return $this->superiors()->data_superrior(
            $superior_id,
            $nama_superior,
            $status_superior,
            $user_id_input_superior,
            $date_input_superior,
        );
    }

    public function add_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        return $this->superiors()->add_superrior(
            $superior_id,
            $nama_superior,
            $status_superior,
            $user_id_input_superior,
            $date_input_superior,
        );
    }

    public function edit_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        return $this->superiors()->edit_superrior(
            $superior_id,
            $nama_superior,
            $status_superior,
            $user_id_input_superior,
            $date_input_superior,
        );
    }

    public function delete_superrior(
        $superior_id,
        $nama_superior,
        $status_superior,
        $user_id_input_superior,
        $date_input_superior,
    ) {
        return $this->superiors()->delete_superrior(
            $superior_id,
            $nama_superior,
            $status_superior,
            $user_id_input_superior,
            $date_input_superior,
        );
    }

    public function get_next_superior_id()
    {
        return $this->superiors()->get_next_superior_id();
    }

    public function superior_id_exists($superior_id)
    {
        return $this->superiors()->superior_id_exists($superior_id);
    }

    public function nama_superior_exists(
        $nama_superior,
        $exclude_superior_id = "",
    ) {
        return $this->superiors()->nama_superior_exists(
            $nama_superior,
            $exclude_superior_id,
        );
    }

    // --- RcvWhRepository ---

    public function data_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->rcvWhs()->data_rcv_wh(
            $id_rcv_wh,
            $id_form_detail,
            $rcv_wh_date,
            $rcv_wh_id_input,
            $rcv_wh_date_input,
        );
    }

    public function add_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->rcvWhs()->add_rcv_wh(
            $id_rcv_wh,
            $id_form_detail,
            $rcv_wh_date,
            $rcv_wh_id_input,
            $rcv_wh_date_input,
        );
    }

    public function edit_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->rcvWhs()->edit_rcv_wh(
            $id_rcv_wh,
            $id_form_detail,
            $rcv_wh_date,
            $rcv_wh_id_input,
            $rcv_wh_date_input,
        );
    }

    public function delete_rcv_wh(
        $id_rcv_wh,
        $id_form_detail,
        $rcv_wh_date,
        $rcv_wh_id_input,
        $rcv_wh_date_input,
    ) {
        return $this->rcvWhs()->delete_rcv_wh(
            $id_rcv_wh,
            $id_form_detail,
            $rcv_wh_date,
            $rcv_wh_id_input,
            $rcv_wh_date_input,
        );
    }

    // --- RcvToolRepository ---

    public function data_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        return $this->rcvTools()->data_rcv_tool(
            $id_rcv_tool,
            $id_form_detail,
            $rcv_tool_date,
            $rcv_tool_id_input,
            $rcv_tool_date_input,
        );
    }

    public function add_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        return $this->rcvTools()->add_rcv_tool(
            $id_rcv_tool,
            $id_form_detail,
            $rcv_tool_date,
            $rcv_tool_id_input,
            $rcv_tool_date_input,
        );
    }

    public function edit_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        return $this->rcvTools()->edit_rcv_tool(
            $id_rcv_tool,
            $id_form_detail,
            $rcv_tool_date,
            $rcv_tool_id_input,
            $rcv_tool_date_input,
        );
    }

    public function delete_rcv_tool(
        $id_rcv_tool,
        $id_form_detail,
        $rcv_tool_date,
        $rcv_tool_id_input,
        $rcv_tool_date_input,
    ) {
        return $this->rcvTools()->delete_rcv_tool(
            $id_rcv_tool,
            $id_form_detail,
            $rcv_tool_date,
            $rcv_tool_id_input,
            $rcv_tool_date_input,
        );
    }

    function __destruct()
    {
        if (
            isset($this->mysqli->conn) &&
            $this->mysqli->conn instanceof mysqli
        ) {
            $this->mysqli->conn->close();
        }
    }
}

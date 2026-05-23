<?php

class DbTable
{
    protected $tb_user = "tb_users";
    protected $tb_action_note = "tb_action_note";
    protected $tb_form = "tb_form";
    protected $tb_form_detail = "tb_form_detail";
    protected $tb_po = "tb_po";
    protected $tb_so = "tb_so";
    protected $tb_superior = "tb_superior";
    protected $tb_rcv_tool = "tb_rcv_tool";
    protected $tb_rcv_wh = "tb_rcv_wh";

    protected $sql_select_distinct = "SELECT DISTINCT ";
    protected $sql_select = "SELECT * FROM ";
    protected $sql_insert = "INSERT INTO ";
    protected $sql_update = "UPDATE ";
    protected $sql_delete = "DELETE FROM ";
    protected $sql_select_count = "SELECT COUNT";
    protected $sql_select_sum = "SELECT SUM";
}

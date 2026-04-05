<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && @$_POST["param"] != null) {
    require_once "../conn/conn.php";
    require_once "../model/dbs.php";

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $result = [];
    $data = new Proses_sql($connection);

    @$param = $_POST["param"];
    @$id_users = $_POST["id_users"];
    @$id_user_post = $_POST["id_users"];
    @$username = $_POST["username"];
    @$password_post = $_POST["password"];
    @$password = md5(@$password_post);
    @$nama_user = $_POST["nama_user"];
    @$foto = $_POST["foto"];
    @$id_tu = $_POST["id_tu"];
    @$no_telp = $_POST["no_telp"];
    @$token = $_POST["token"];
    @$level = $_POST["level"];
    @$status = $_POST["status"];
    @$add_data_user = "ADD DATA USER";
    @$edit_data_user = "EDIT DATA USER";
    @$view_data_user = "VIEW DATA USER";
    @$delete_data_user = "DELETED DATA USER";

    if (
        @$param == @$add_data_user ||
        @$param == @$edit_data_user ||
        @$param == @$view_data_user
    ) {
        @$data_user = $data->data_user(
            @$param == @$add_data_user || @$param == @$edit_data_user
                ? ""
                : @$id_users,
            @$username,
            @$nama_user,
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        );
        if (@$param == @$add_data_user || @$param == @$edit_data_user) {
            @$row_user_cek = $data_user->fetch_object();
            @$id_users_cek = $row_user_cek->id_users;
            @$username_cek = $row_user_cek->username;
        } elseif (@$param == @$view_data_user) {
            while (@$row_user = $data_user->fetch_object()) {
                if (isset($row_user)) {
                    @$id_users = $row_user->id_users;
                    @$username = $row_user->username;
                    @$password = $row_user->password;
                    @$nama_user = $row_user->nama_user;
                    @$foto = $row_user->foto;
                    @$id_tu = $row_user->id_tu;
                    @$no_telp = $row_user->no_telp;
                    @$token = $row_user->token;
                    @$level = $row_user->level;
                    @$status = $row_user->status;
                } else {
                    @$id_users = "";
                    @$username = "";
                    @$password = "";
                    @$nama_user = "";
                    @$foto = "";
                    @$id_tu = "";
                    @$no_telp = "";
                    @$token = "";
                    @$level = "";
                    @$status = "";
                }
                $b["id_users"] = $id_users;
                $b["username"] = $username;
                $b["password"] = $password;
                $b["nama_user"] = $nama_user;
                $b["foto"] = $foto;
                $b["id_tu"] = $id_tu;
                $b["no_telp"] = $no_telp;
                $b["no_telp"] = $no_telp;
                $b["token"] = $token;
                $b["level"] = $level;
                $b["status"] = $status;

                array_push($result, $b);
            }
        }
    }

    switch ($param) {
        case $add_data_user:
            if (isset($row_user_cek)) {
                $response["value"] = "0";
                $response["message"] = "DATA USER AVAILABLE";
            } else {
                @$add_user = $data->add_user(
                    @$id_users,
                    @$username,
                    @$password,
                    @$nama_user,
                    @$foto,
                    @$id_tu,
                    @$no_telp,
                    @$token,
                    @$level,
                    @$status,
                );
                if ($add_user) {
                    $response["value"] = "1";
                    $response["message"] = "$param SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param  FAILED";
                }
            }
            break;
        case $edit_data_user:
            if (
                @$id_users_cek != @$id_user_post &&
                $username == $username_cek
            ) {
                $response["value"] = "0";
                $response[
                    "message"
                ] = "USERNAME DUPLICATE $username $username_cek $id_user_post  $id_users_cek !";
            } elseif (@$id_users == null || @$id_users == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                $edit_user = 1;
                @$edit_user = $data->edit_user(
                    @$id_users,
                    @$username,
                    @$password,
                    @$nama_user,
                    @$foto,
                    @$id_tu,
                    @$no_telp,
                    @$token,
                    @$level,
                    @$status,
                );
                if ($edit_user) {
                    $response["value"] = "1";
                    $response[
                        "message"
                    ] = "$param $username $username_cek $id_user_post  $id_users_cek SUCCESS";
                } else {
                    $response["value"] = "0";
                    $response["message"] = "$param FAILED";
                }
            }
            break;
        case @$delete_data_user:
            if (@$id_users == null || @$id_users == "") {
                $response["value"] = "0";
                $response["message"] = "ERROR $param !";
            } else {
                @$delete_user = $data->delete_user(@$id_users);
            }
            if (@$delete_user) {
                $response["value"] = "1";
                $response["message"] = "$param SUCCESS";
            } else {
                $response["value"] = "0";
                $response["message"] = "$param FAILED";
            }
            break;
        default:
            $response["value"] = "2";
            $response["message"] = "$param DATA FAILED";
            break;
    }
    switch ($param) {
        case $add_data_user:
            array_push($result, $response);
            break;
        case $edit_data_user:
            array_push($result, $response);
            break;
        case $delete_data_user:
            array_push($result, $response);
            break;
        default:
            break;
    }
    echo json_encode($result);
}
?>

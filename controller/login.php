<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && @$_POST["param"] != null) {
    require_once "../conn/conn.php";
    require_once "../model/dbs.php";

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $result = [];
    $data = new Proses_sql($connection);
    @$param = $_POST["param"] ?? "";
    $username = $_POST["username"] ?? "";
    $passwordRaw = $_POST["password"] ?? "";
    $password = md5($passwordRaw);

    if ($username === "" || $passwordRaw === "") {
        $response["value"] = "0";
        $response["message"] = "LOGIN FAILED ";
    } else {
        $user = $data->login($username, $password);
        $data_user =
            $user && $user->num_rows > 0 ? $user->fetch_object() : null;
        if ($data_user) {
            $id_users = $data_user->id_users;
            $username = $data_user->username;
            $password = $data_user->password;
            $nama_user = $data_user->nama_user;
            $foto = $data_user->foto;
            $id_tu = $data_user->id_tu;
            $no_telp = $data_user->no_telp;
            $token = $data_user->token;
            $level = $data_user->level;
            $status = $data_user->status;
            $superior_id = $data_user->superior_id;
            $nama_superior = $data_user->nama_superior ?? "";

            // Cek apakah nama_user terdaftar sebagai nama_superior di tb_superior.
            $superior_from_nama = $data->data_superrior(
                "",
                $nama_user,
                "",
                "",
                "",
            );
            if ($superior_from_nama && $superior_from_nama->num_rows > 0) {
                $superior_data = $superior_from_nama->fetch_object();
                if (!empty($superior_data->superior_id)) {
                    $superior_id = $superior_data->superior_id;
                }
            }

            $response["value"] = "1";
            $response["message"] = "LOGIN SUCCESS";
            $response["id_users"] = strval($id_users);
            $response["username"] = $username;
            $response["password"] = $password;
            $response["nama_user"] = $nama_user;
            $response["foto"] = $foto;
            $response["id_tu"] = $id_tu;
            $response["no_telp"] = $no_telp;
            $response["token"] = $token;
            $response["level"] = $level;
            $response["status"] = $status;
            $response["superior_id"] = $superior_id;
            $response["nama_superior"] = $nama_superior;
        } else {
            $response["value"] = "0";
            $response["message"] = "LOGIN FAILED";
        }
    }
    echo json_encode($response);
}
?>

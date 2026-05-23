<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && @$_POST["param"] != null) {
    require_once "../conn/conn.php";
    require_once "../conn/password.php";
    require_once "../conn/rate_limit.php";
    require_once "../model/dbs.php";

    login_rate_limit_check();

    $connection = new Dbs($host, $user, $pass, $db);
    include "../model/m_proses.php";
    $result = [];
    $data = new Proses_sql($connection);
    @$param = $_POST["param"] ?? "";
    $username = $_POST["username"] ?? "";
    $passwordRaw = $_POST["password"] ?? "";

    if ($username === "" || $passwordRaw === "") {
        $response["value"] = "0";
        $response["message"] = "LOGIN FAILED ";
    } else {
        $userResult = $data->find_user_by_username($username);
        $data_user =
            $userResult && $userResult->num_rows > 0
                ? $userResult->fetch_object()
                : null;

        if (
            $data_user &&
            password_verify_login($passwordRaw, (string) $data_user->password)
        ) {
            if (password_is_legacy_md5((string) $data_user->password)) {
                $data->update_user_password(
                    (string) $data_user->id_users,
                    password_hash_for_storage($passwordRaw),
                );
            }

            $id_users = $data_user->id_users;
            $username = $data_user->username;
            $nama_user = $data_user->nama_user;
            $foto = $data_user->foto;
            $id_tu = $data_user->id_tu;
            $no_telp = $data_user->no_telp;
            $token = $data_user->token;
            $level = $data_user->level;
            $status = $data_user->status;
            $superior_id = $data_user->superior_id;
            $nama_superior = $data_user->nama_superior ?? "";

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

            login_rate_limit_clear();

            $response["value"] = "1";
            $response["message"] = "LOGIN SUCCESS";
            $response["id_users"] = strval($id_users);
            $response["username"] = $username;
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

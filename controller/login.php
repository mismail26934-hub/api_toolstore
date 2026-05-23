<?php
require_once __DIR__ . "/../conn/api_bootstrap.php";

if (!api_is_post_with_param()) {
    return;
}

$data = api_bootstrap_login();
$param = api_post_param();
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

        $superior_from_nama = $data->data_superrior("", $nama_user, "", "", "");
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
api_json_response($response);
?>

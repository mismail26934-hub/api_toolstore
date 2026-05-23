<?php
class Dbs
{
    private $host, $user, $pass, $db;
    public $conn;

    function __construct($host, $user, $pass, $db)
    {
        $this->host = $host;
        $this->pass = $pass;
        $this->db = $db;
        $this->user = $user;

        $this->conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->db,
        );

        if ($this->conn->connect_errno) {
            error_log(
                "MySQL connect failed [" .
                    $this->conn->connect_errno .
                    "]: " .
                    $this->conn->connect_error,
            );
            if (!headers_sent()) {
                http_response_code(500);
            }
            die("Database connection failed.");
        }
    }
}
?>

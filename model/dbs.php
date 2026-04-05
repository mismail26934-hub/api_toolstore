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

        if ($this->conn->connect_error) {
            die($this->conn->connect_error);
        }
    }
}
?>

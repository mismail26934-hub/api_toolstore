<?php

require_once __DIR__ . "/db_table.php";
require_once __DIR__ . "/db_statement.php";

abstract class RepositoryBase extends DbTable
{
    use DbStatementTrait;

    /** @var Dbs */
    protected $mysqli;

    public function __construct($conn)
    {
        $this->mysqli = $conn;
    }
}

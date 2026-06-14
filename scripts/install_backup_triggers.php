<?php
/**
 * Install backup triggers for 8 main tables.
 *
 * Usage:
 *   php scripts/install_backup_triggers.php --dry-run
 *   php scripts/install_backup_triggers.php --execute
 */

declare(strict_types=1);

require_once __DIR__ . "/../conn/env_loader.php";

load_dotenv(__DIR__ . "/../.env");

$host = require_env("DB_HOST");
$user = require_env("DB_USER");
$pass = env_value("DB_PASS") ?: "";
$dbName = require_env("DB_NAME");

$dryRun = in_array("--dry-run", $argv, true);
$execute = in_array("--execute", $argv, true);

if (!$dryRun && !$execute) {
    fwrite(STDERR, "Usage: php scripts/install_backup_triggers.php --dry-run|--execute\n");
    exit(1);
}

/** @var array<string, array{history: string, delete: string}> */
$tableMap = [
    "tb_form" => ["history" => "tb_form_history", "delete" => "tb_form_delete"],
    "tb_form_detail" => [
        "history" => "tb_form_detail_history",
        "delete" => "tb_form_detail_delete",
    ],
    "tb_po" => ["history" => "tb_po_history", "delete" => "tb_po_delete"],
    "tb_rcv_tool" => [
        "history" => "tb_rcv_tool_history",
        "delete" => "tb_rcv_tool_delete",
    ],
    "tb_rcv_wh" => ["history" => "tb_rcv_wh_history", "delete" => "tb_rcv_wh_delete"],
    "tb_so" => ["history" => "tb_so_history", "delete" => "tb_so_delete"],
    "tb_superior" => [
        "history" => "tb_superior_history",
        "delete" => "tb_superior_delete",
    ],
    "tb_users" => ["history" => "tb_users_history", "delete" => "tb_users_delete"],
];

$mysqli = new mysqli($host, $user, $pass, $dbName);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Connect failed: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset("utf8mb4");

function tableExists(mysqli $db, string $table): bool
{
    $stmt = $db->prepare(
        "SELECT 1 FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1",
    );
    $stmt->bind_param("s", $table);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_row();
}

/** @return list<array{name: string, extra: string}> */
function getColumns(mysqli $db, string $table): array
{
    $result = $db->query("SHOW COLUMNS FROM `$table`");
    if ($result === false) {
        throw new RuntimeException("SHOW COLUMNS gagal untuk $table: {$db->error}");
    }

    $cols = [];
    while ($row = $result->fetch_assoc()) {
        $cols[] = [
            "name" => (string) $row["Field"],
            "extra" => strtolower((string) ($row["Extra"] ?? "")),
        ];
    }

    return $cols;
}

/** @return array{0: list<string>, 1: list<string>} */
function buildInsertParts(
    mysqli $db,
    string $sourceTable,
    string $backupTable,
    string $actionType,
): array {
    $sourceCols = array_column(getColumns($db, $sourceTable), "name");
    $sourceSet = array_flip($sourceCols);

    $insertCols = [];
    $insertVals = [];

    foreach (getColumns($db, $backupTable) as $col) {
        $name = $col["name"];

        if (str_contains($col["extra"], "auto_increment")) {
            continue;
        }

        if (isset($sourceSet[$name])) {
            $insertCols[] = "`$name`";
            $insertVals[] = "OLD.`$name`";
            continue;
        }

        if ($name === "action_at") {
            $insertCols[] = "`action_at`";
            $insertVals[] = "NOW()";
            continue;
        }

        if ($name === "action_by") {
            $insertCols[] = "`action_by`";
            $insertVals[] = "@app_user";
            continue;
        }

        if ($name === "action_type") {
            $insertCols[] = "`action_type`";
            $insertVals[] =
                "'" . $db->real_escape_string($actionType) . "'";
        }
    }

    if ($insertCols === []) {
        throw new RuntimeException("Tidak ada kolom insert untuk $backupTable");
    }

    return [$insertCols, $insertVals];
}

function buildRowChangedCondition(mysqli $db, string $sourceTable): string
{
    $checks = [];

    foreach (getColumns($db, $sourceTable) as $col) {
        if (str_contains($col["extra"], "auto_increment")) {
            continue;
        }

        $name = $col["name"];
        $checks[] = "OLD.`$name` <=> NEW.`$name`";
    }

    if ($checks === []) {
        return "TRUE";
    }

    return "NOT (\n        " . implode("\n        AND ", $checks) . "\n    )";
}

function buildTriggerSql(
    mysqli $db,
    string $sourceTable,
    string $backupTable,
    string $timing,
    string $actionType,
): string {
    [$cols, $vals] = buildInsertParts($db, $sourceTable, $backupTable, $actionType);

    $triggerName = sprintf(
        "trg_%s_before_%s",
        $sourceTable,
        strtolower($timing),
    );

    $colList = implode(",\n        ", $cols);
    $valList = implode(",\n        ", $vals);

    $insertSql = <<<SQL
    INSERT INTO `$backupTable` (
        $colList
    ) VALUES (
        $valList
    );
SQL;

    if ($timing === "UPDATE") {
        $changeCondition = buildRowChangedCondition($db, $sourceTable);
        $body = <<<SQL
    IF $changeCondition THEN
$insertSql
    END IF;
SQL;
    } else {
        $body = $insertSql;
    }

    return <<<SQL
DROP TRIGGER IF EXISTS `$triggerName`;
CREATE TRIGGER `$triggerName`
BEFORE $timing ON `$sourceTable`
FOR EACH ROW
BEGIN
$body
END;
SQL;
}

/** @return array{0: string, 1: string} */
function splitTriggerBlock(string $block): array
{
    if (
        !preg_match(
            "/^(DROP TRIGGER IF EXISTS `[^`]+`;)\s*(CREATE TRIGGER .+)$/s",
            trim($block),
            $matches,
        )
    ) {
        throw new RuntimeException("Invalid trigger block");
    }

    return [$matches[1], $matches[2]];
}

$blocks = [];

foreach ($tableMap as $source => $targets) {
    foreach (["history" => "UPDATE", "delete" => "DELETE"] as $kind => $timing) {
        $backup = $targets[$kind];

        if (!tableExists($mysqli, $source)) {
            throw new RuntimeException("Tabel sumber tidak ada: $source");
        }
        if (!tableExists($mysqli, $backup)) {
            throw new RuntimeException("Tabel backup tidak ada: $backup");
        }

        $actionType = $timing === "UPDATE" ? "UPDATE" : "DELETE";
        $blocks[] = buildTriggerSql(
            $mysqli,
            $source,
            $backup,
            $timing,
            $actionType,
        );
    }
}

$output = implode(";\n\n", $blocks) . ";\n";

if ($dryRun) {
    echo $output;
    exit(0);
}

foreach ($blocks as $block) {
    [$dropSql, $createSql] = splitTriggerBlock($block);

    if (!$mysqli->query($dropSql)) {
        fwrite(STDERR, "Gagal DROP: {$mysqli->error}\nSQL:\n$dropSql\n");
        exit(1);
    }
    echo "OK: " . strtok($dropSql, "\n") . "\n";

    if (!$mysqli->query($createSql)) {
        fwrite(STDERR, "Gagal CREATE: {$mysqli->error}\nSQL:\n$createSql\n");
        exit(1);
    }
    echo "OK: " . strtok($createSql, "\n") . "\n";
}

echo "Selesai. " . count($blocks) . " trigger terpasang.\n";

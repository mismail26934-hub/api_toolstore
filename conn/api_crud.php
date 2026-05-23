<?php

/**
 * Helper response CRUD — format kontrak API lama.
 */

/**
 * Ambil semua baris dari mysqli_result ke array (hindari result set tertimpa query berikutnya).
 *
 * @param mysqli_result|bool $result
 * @return list<object>
 */
function api_mysqli_fetch_all_objects(mysqli_result|bool $result): array
{
    if (!($result instanceof mysqli_result)) {
        return [];
    }

    $assoc = $result->fetch_all(MYSQLI_ASSOC);
    if ($assoc !== false && $assoc !== []) {
        return array_map(
            static fn(array $row): object => (object) $row,
            $assoc,
        );
    }

    $rows = [];
    $result->data_seek(0);
    while ($row = $result->fetch_object()) {
        if ($row !== null) {
            $rows[] = $row;
        }
    }

    return $rows;
}

/** Baca kolom COUNT dari hasil query (default: cnt). */
function api_crud_count_from_query(
    mysqli_result|bool $countResult,
    string $column = "cnt",
): int {
    if (!($countResult instanceof mysqli_result)) {
        return 0;
    }

    $row = $countResult->fetch_object();
    if ($row === null || !isset($row->{$column})) {
        $countResult->free();

        return 0;
    }

    $count = (int) $row->{$column};
    $countResult->free();

    return $count;
}

/**
 * @return array{value: string, message: string}
 */
function api_crud_fail(string $message): array
{
    return ["value" => "0", "message" => $message];
}

/**
 * @return array{value: string, message: string}
 */
function api_crud_ok(
    string $param,
    bool $success,
    string $failSuffix = "FAILED",
): array {
    return [
        "value" => $success ? "1" : "0",
        "message" => $success
            ? $param . " SUCCESS"
            : $param . " " . $failSuffix,
    ];
}

/**
 * @return array{value: string, message: string}
 */
function api_crud_unknown_param(string $param): array
{
    return ["value" => "2", "message" => $param . " DATA FAILED"];
}

/** Push response mutasi ke array hasil (ADD/EDIT/DELETE). */
function api_crud_push_mutation(
    array &$result,
    string $param,
    array $response,
    string $addParam,
    string $editParam,
    string $deleteParam,
): void {
    if (
        $param === $addParam ||
        $param === $editParam ||
        $param === $deleteParam
    ) {
        $result[] = $response;
    }
}

/** Output JSON: list dengan total atau array mutasi. */
function api_crud_emit(
    string $param,
    string $viewParam,
    array $result,
    ?int $total = null,
): void {
    if ($param === $viewParam) {
        echo json_encode(
            [
                "total" => $total ?? 0,
                "data" => $result,
            ],
            JSON_UNESCAPED_UNICODE,
        );
        return;
    }

    echo json_encode($result);
}

<?php

/**
 * Alias role logis → satu atau lebih nilai tb_users.level.
 * Tambahkan variasi penulisan level di sini tanpa mengubah tiap milestone.
 *
 * @return array<string, list<string>>
 */
return [
    "service_admin" => ["service_admin", "SERVICE_ADMIN"],
    "dept_head" => ["dept_head", "HEAD_SERVICE"],
    "counter_ga" => ["counter_ga", "COUNTER_GA", "COUNTER", "GA"],
    "wh_ga" => ["wh_ga", "WH_GA", "WH", "GA"],
    "tool_store" => ["tool_store", "TOOL_STORE", "TOOL_KEEPER"],
];

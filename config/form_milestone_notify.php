<?php

/**
 * Konfigurasi notifikasi WhatsApp form_milestone.
 *
 * recipients (milestone):
 *   - superior         → no_telp superior serviceman
 *   - role:<role>      → role logis (config/user_level_roles.php)
 *   - level:<level>    → tb_users.level eksak (opsional)
 *
 * Serviceman (form_serv_name) **selalu** dapat pesan info terpisah di **semua**
 * milestone yang terdaftar di bawah, terlepas dari recipients milestone.
 * Matikan via defaults.always_notify_serviceman = false atau env NOTIFY_SERVICEMAN_ALWAYS=0.
 *
 * Placeholder: {form_no}, {milestone}, {serviceman}, {superior}, {dept_head},
 *               {comment}, {label}, {form_link}, {parts}
 *
 * {dept_head} → nama_user tb_users dengan role dept_head (user_level_roles.php)
 *
 * {label}  → teks status ramah pengguna (kolom Notifikasi)
 * {milestone} → alias {label} di pesan WhatsApp
 *
 * parts_filter (per milestone, opsional):
 *   - all       → semua baris tb_form_detail (default)
 *   - milestone → baris dengan form_detail_milestone = milestone form;
 *                 jika kosong, fallback ke part yang punya record di
 *                 tb_rcv_wh (milestone WH/GA) atau tb_rcv_tool (TOOL STORE)
 *
 * parts_max_lines (per milestone, opsional): batas baris part; 0 = tanpa batas
 * parts_rcv_wh_dates (per milestone, opsional): tampilkan semua rcv_wh_date (d/m/Y)
 * parts_rcv_tool_dates (per milestone, opsional): tampilkan semua rcv_tool_date (d/m/Y)
 * parts_po_so (per milestone, opsional): tampilkan PO (tb_po) & SO (tb_so) per part
 *
 * @return array{
 *   defaults: array<string, mixed>,
 *   milestones: array<string, array{
 *     label: string,
 *     recipients: list<string>,
 *     template: string,
 *     comment_field?: string,
 *     parts_filter?: string,
 *     parts_max_lines?: int,
 *     parts_rcv_wh_dates?: bool,
 *     parts_rcv_tool_dates?: bool,
 *     parts_po_so?: bool
 *   }>
 * }
 */
return [
    "defaults" => [
        "always_notify_serviceman" => true,
        "parts_max_lines" => 10,
        "message_footer" => "\n\nLihat form: {form_link}",
        "serviceman_info_template" =>
            "ℹ️ *Informasi Tool Store*\n" .
            "Form: {form_no}\n" .
            "Status: {label}\n" .
            "Serviceman: {serviceman}\n" .
            "*Detail Part:*\n{parts}\n" .
            "Lihat form: {form_link}",
    ],

    "milestones" => [
        "CHECK BY TOOL STORE" => [
            "label" => "Menunggu Approval Atasan",
            "recipients" => ["superior"],
            "template" =>
                "🔔 *{label}*\n" .
                "Form: {form_no}\n" .
                "Status: {label}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}\n" .
                "Yth. {superior}, Mohon dicek & approve.",
        ],

        "SUPERIOR APPROVED" => [
            "label" => "Direview oleh Service Admin",
            "recipients" => ["role:service_admin"],
            "template" =>
                "📋 *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}\n" .
                "Team Service Admin, Mohon direview.",
        ],

        "REJECTED BY SUPERIOR" => [
            "label" => "Ditolak oleh Atasan",
            "recipients" => [],
            "comment_field" => "form_superior_comment",
            "template" =>
                "❌ *{label}*\n" .
                "Form: {form_no}\n" .
                "Catatan: {comment}\n" .
                "*Detail Part:*\n{parts}\n",
        ],

        "REVIEWED BY SERVICE ADMIN" => [
            "label" => "Menunggu Approval Dept. Head",
            "recipients" => ["role:dept_head"],
            "template" =>
                "📋 *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}\n" .
                "Yth. {dept_head}, Mohon dicek & approve.",
        ],

        "HOLD BY SERVICE ADMIN" => [
            "label" => "Ditahan oleh Service Admin",
            "recipients" => [],
            "comment_field" => "form_sadmin_comment",
            "template" =>
                "⏸️ *{label}*\n" . "Form: {form_no}\n" . "Catatan: {comment}",
        ],

        "APPROVED BY SERVICE DEPT HEAD" => [
            "label" => "Menunggu Proses Order",
            "recipients" => ["role:counter_ga", "role:wh_ga"],
            "template" =>
                "✅ *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}\n" .
                "Yth. Team Counter / GA, Mohon diproses order.",
        ],

        "REJECTED BY SERVICE DEPT HEAD" => [
            "label" => "Ditolak oleh Dept. Head",
            "recipients" => [],
            "comment_field" => "form_shead_comment",
            "template" =>
                "❌ *{label}*\n" . "Form: {form_no}\n" . "Catatan: {comment}",
        ],

        "PROCESSING ORDER" => [
            "label" => "Tool Sedang Diproses Order",
            "recipients" => [],
            "parts_max_lines" => 0,
            "parts_po_so" => true,
            "template" =>
                "📦 *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}",
        ],

        "ORDER PROCESSED" => [
            "label" => "Tool Sudah Diproses Order",
            "recipients" => ["role:wh_ga", "role:tool_store"],
            "parts_max_lines" => 0,
            "parts_po_so" => true,
            "template" =>
                "✅ *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}",
        ],

        "RECEIVED BY WH/GA" => [
            "label" => "Diterima oleh Warehouse/GA",
            "recipients" => ["role:tool_store"],
            "parts_max_lines" => 0,
            "parts_rcv_wh_dates" => true,
            "template" =>
                "📥 *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}",
        ],

        "PARTIAL RECEIVED BY WH/GA" => [
            "label" => "Diterima Sebagian oleh Warehouse/GA",
            "recipients" => ["role:tool_store"],
            "parts_max_lines" => 0,
            "parts_rcv_wh_dates" => true,
            "template" =>
                "📥 *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}",
        ],

        "RECEIVED BY TOOL STORE" => [
            "label" => "Diterima Complete oleh Tool Store",
            "recipients" => [],
            "parts_max_lines" => 0,
            "parts_rcv_tool_dates" => true,
            "template" =>
                "🏁 *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}",
        ],

        "PARTIAL RECEIVED BY TOOL STORE" => [
            "label" => "Diterima Sebagian oleh Tool Store",
            "recipients" => [],
            "parts_max_lines" => 0,
            "parts_rcv_tool_dates" => true,
            "template" =>
                "🏁 *{label}*\n" .
                "Form: {form_no}\n" .
                "Serviceman: {serviceman}\n" .
                "*Detail Part:*\n{parts}",
        ],
    ],
];

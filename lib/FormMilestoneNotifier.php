<?php

require_once __DIR__ . "/FormMilestone.php";
require_once __DIR__ . "/UserLevelRoles.php";
require_once __DIR__ . "/WhacenterClient.php";
require_once __DIR__ . "/../conn/env_loader.php";

/**
 * Kirim notifikasi WhatsApp saat form_milestone berubah.
 */
class FormMilestoneNotifier
{
    private const DEFAULT_MONITORING_URL = "https://strakin.tech/Tool-Monitoring/";

    private const DEFAULT_PARTS_MAX_LINES = 10;

    private Proses_sql $data;

    private WhacenterClient $client;

    private UserLevelRoles $levelRoles;

    /** @var array<string, mixed> */
    private array $defaults;

    /** @var array<string, array<string, mixed>> */
    private array $rules;

    public function __construct(
        Proses_sql $data,
        ?WhacenterClient $client = null,
        ?UserLevelRoles $levelRoles = null,
    ) {
        $this->data = $data;
        $this->client = $client ?? new WhacenterClient();
        $this->levelRoles = $levelRoles ?? new UserLevelRoles();

        $config = require dirname(__DIR__) .
            "/config/form_milestone_notify.php";
        $this->defaults = $config["defaults"] ?? [];
        $this->rules = $config["milestones"] ?? $config;
    }

    /**
     * @param array<string, mixed> $form
     */
    public function notifyIfChanged(array $form, ?string $oldMilestone): void
    {
        if (!WhacenterClient::isEnabled()) {
            return;
        }

        try {
            $newNorm = FormMilestone::normalize($form["form_milestone"] ?? "");
            $oldNorm = FormMilestone::normalize($oldMilestone);

            if ($newNorm === "" || $newNorm === $oldNorm) {
                return;
            }

            if (!isset($this->rules[$newNorm])) {
                return;
            }

            $rule = $this->rules[$newNorm];
            $formServName = (string) ($form["form_serv_name"] ?? "");
            $replacements = $this->buildReplacements($newNorm, $rule, $form);

            $this->notifyActionRecipients(
                $rule,
                $replacements,
                $formServName,
                $form,
                $newNorm,
            );

            $this->notifyServicemanOnAllMilestones(
                $replacements,
                $formServName,
                $form,
                $newNorm,
            );
        } catch (Throwable $e) {
            error_log(
                "FormMilestoneNotifier: " .
                    $e->getMessage() .
                    " form=" .
                    ($form["id_form"] ?? ""),
            );
        }
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, mixed> $form
     * @return array<string, string>
     */
    private function buildReplacements(
        string $normalized,
        array $rule,
        array $form,
    ): array {
        $commentField = (string) ($rule["comment_field"] ?? "");
        $comment =
            $commentField !== ""
                ? trim((string) ($form[$commentField] ?? ""))
                : "";
        if ($comment === "") {
            $comment = "-";
        }

        $formNo = (string) ($form["form_no"] ?? "-");
        $formServName = (string) ($form["form_serv_name"] ?? "");
        $ctx = $this->servicemanContext($formServName);
        $superior = trim((string) ($ctx?->nama_superior ?? ""));
        if ($superior === "") {
            $superior = "-";
        }

        $deptHead = $this->roleDisplayName("dept_head");
        if ($deptHead === "") {
            $deptHead = "-";
        }

        $idForm = trim((string) ($form["id_form"] ?? ""));
        $label = (string) ($rule["label"] ?? $normalized);

        return [
            "{form_no}" => $formNo,
            "{milestone}" => $label,
            "{serviceman}" => $formServName !== "" ? $formServName : "-",
            "{superior}" => $superior,
            "{dept_head}" => $deptHead,
            "{comment}" => $comment,
            "{label}" => $label,
            "{form_link}" => $this->formLink($formNo),
            "{parts}" => $this->formatPartsList($idForm, $normalized, $rule),
        ];
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function formatPartsList(
        string $idForm,
        string $normalizedMilestone,
        array $rule,
    ): string {
        if ($idForm === "") {
            return "-";
        }

        $result = $this->data->data_form_detail(
            "",
            $idForm,
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
        );
        if (!($result instanceof mysqli_result)) {
            return "-";
        }

        $partsFilter = (string) ($rule["parts_filter"] ?? "all");
        $maxLines = $this->resolvePartsMaxLines($rule);

        $rows = [];
        while ($row = $result->fetch_object()) {
            $rows[] = $row;
        }
        $result->free();

        $matchedRows = $this->filterPartRows(
            $rows,
            $partsFilter,
            $normalizedMilestone,
            $idForm,
        );

        $rcvWhDatesByPart = $this->shouldShowRcvWhDates($rule)
            ? $this->loadRcvWhDatesByPart($idForm)
            : [];

        $rcvToolDatesByPart = $this->shouldShowRcvToolDates($rule)
            ? $this->loadRcvToolDatesByPart($idForm)
            : [];

        $poNosByPart = [];
        $soEntriesByPart = [];
        if ($this->shouldShowPoSo($rule)) {
            $poNosByPart = $this->data->po_nos_by_form($idForm);
            $soEntriesByPart = $this->data->so_entries_by_form($idForm);
        }

        $lines = [];
        $totalMatched = count($matchedRows);
        foreach ($matchedRows as $row) {
            if ($maxLines > 0 && count($lines) >= $maxLines) {
                break;
            }

            $partId = trim((string) ($row->id_form_detail ?? ""));
            $lines[] = $this->formatPartLine(count($lines) + 1, $row, [
                "rcv_wh_dates" => $rcvWhDatesByPart[$partId] ?? [],
                "rcv_tool_dates" => $rcvToolDatesByPart[$partId] ?? [],
                "po_nos" => $poNosByPart[$partId] ?? [],
                "so_entries" => $soEntriesByPart[$partId] ?? [],
            ]);
        }

        if ($lines === []) {
            return "-";
        }

        $text = implode("\n", $lines);
        $remaining = $totalMatched - count($lines);
        if ($remaining > 0) {
            $text .= "\n...dan {$remaining} part lainnya";
        }

        return $text;
    }

    /**
     * @param list<object> $rows
     * @return list<object>
     */
    private function filterPartRows(
        array $rows,
        string $partsFilter,
        string $normalizedMilestone,
        string $idForm,
    ): array {
        if ($partsFilter !== "milestone") {
            return $rows;
        }

        $byMilestone = [];
        foreach ($rows as $row) {
            $detailMile = FormMilestone::normalize(
                $row->form_detail_milestone ?? "",
            );
            if ($detailMile === $normalizedMilestone) {
                $byMilestone[] = $row;
            }
        }
        if ($byMilestone !== []) {
            return $byMilestone;
        }

        $receiveIds = $this->receivedPartIdsForMilestone(
            $idForm,
            $normalizedMilestone,
        );
        if ($receiveIds === []) {
            return [];
        }

        $filtered = [];
        foreach ($rows as $row) {
            $id = trim((string) ($row->id_form_detail ?? ""));
            if ($id !== "" && isset($receiveIds[$id])) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }

    /**
     * @return array<string, true>
     */
    private function receivedPartIdsForMilestone(
        string $idForm,
        string $normalizedMilestone,
    ): array {
        $ids = [];
        if (str_contains($normalizedMilestone, "WH/GA")) {
            foreach (
                $this->data->rcv_wh_form_detail_ids_by_form($idForm)
                as $id
            ) {
                $ids[$id] = true;
            }
        } elseif (str_contains($normalizedMilestone, "TOOL STORE")) {
            foreach (
                $this->data->rcv_tool_form_detail_ids_by_form($idForm)
                as $id
            ) {
                $ids[$id] = true;
            }
        }

        return $ids;
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function resolvePartsMaxLines(array $rule): int
    {
        if (array_key_exists("parts_max_lines", $rule)) {
            return max(0, (int) $rule["parts_max_lines"]);
        }

        $maxLines =
            (int) ($this->defaults["parts_max_lines"] ??
                self::DEFAULT_PARTS_MAX_LINES);

        return $maxLines < 1 ? self::DEFAULT_PARTS_MAX_LINES : $maxLines;
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function shouldShowRcvWhDates(array $rule): bool
    {
        $flag = $rule["parts_rcv_wh_dates"] ?? false;

        return $flag === true || $flag === 1 || $flag === "1";
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function shouldShowPoSo(array $rule): bool
    {
        $flag = $rule["parts_po_so"] ?? false;

        return $flag === true || $flag === 1 || $flag === "1";
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function shouldShowRcvToolDates(array $rule): bool
    {
        $flag = $rule["parts_rcv_tool_dates"] ?? false;

        return $flag === true || $flag === 1 || $flag === "1";
    }

    /**
     * @return array<string, list<string>>
     */
    private function loadRcvWhDatesByPart(string $idForm): array
    {
        $raw = $this->data->rcv_wh_dates_by_form($idForm);
        $formatted = [];
        foreach ($raw as $partId => $dates) {
            $lines = [];
            foreach ($dates as $date) {
                $text = $this->formatRcvWhDate($date);
                if ($text !== "") {
                    $lines[] = $text;
                }
            }
            if ($lines !== []) {
                $formatted[$partId] = $lines;
            }
        }

        return $formatted;
    }

    /**
     * @return array<string, list<string>>
     */
    private function loadRcvToolDatesByPart(string $idForm): array
    {
        $raw = $this->data->rcv_tool_dates_by_form($idForm);
        $formatted = [];
        foreach ($raw as $partId => $dates) {
            $lines = [];
            foreach ($dates as $date) {
                $text = $this->formatRcvToolDate($date);
                if ($text !== "") {
                    $lines[] = $text;
                }
            }
            if ($lines !== []) {
                $formatted[$partId] = $lines;
            }
        }

        return $formatted;
    }

    private function formatRcvWhDate(string $raw): string
    {
        return $this->formatNotifyDate($raw);
    }

    private function formatRcvToolDate(string $raw): string
    {
        return $this->formatNotifyDate($raw);
    }

    /**
     * @param array{so: string, eta: string, note_so: string} $entry
     */
    private function formatSoEntry(array $entry): string
    {
        $so = trim((string) ($entry["so"] ?? ""));
        if ($so === "") {
            return "";
        }

        $text = $so;
        $eta = $this->formatNotifyDate(trim((string) ($entry["eta"] ?? "")));
        if ($eta !== "") {
            $text .= " (ETA: {$eta})";
        }

        $note = trim((string) ($entry["note_so"] ?? ""));
        if ($note !== "") {
            $text .= ", Note: {$note}";
        }

        return $text;
    }

    private function formatNotifyDate(string $raw): string
    {
        $raw = trim($raw);
        if (
            $raw === "" ||
            $raw === "0000-00-00" ||
            str_starts_with($raw, "0000-00-00")
        ) {
            return "";
        }

        $ts = strtotime($raw);

        return $ts !== false ? date("d/m/Y", $ts) : $raw;
    }

    /**
     * @param array<string, mixed> $extras
     */
    private function formatPartLine(
        int $index,
        object $row,
        array $extras = [],
    ): string {
        $pn = trim((string) ($row->pn_group ?? ""));
        $desc = trim((string) ($row->pn_desc ?? ""));
        $qty = trim((string) ($row->qty ?? ""));

        $label = $pn;
        if ($desc !== "" && $desc !== $pn) {
            $label = $pn !== "" ? "{$pn} - {$desc}" : $desc;
        }
        if ($label === "") {
            $label = "-";
        }

        $line = "{$index}. {$label}";
        if ($qty !== "" && $qty !== "0") {
            $line .= " (Qty: {$qty})";
        }

        $qtyNum = $this->parsePartNumber($row->qty ?? null);
        $unitPrice = $this->parsePartNumber($row->part_value ?? null);
        if (
            $qtyNum !== null &&
            $unitPrice !== null &&
            $qtyNum > 0 &&
            $unitPrice > 0
        ) {
            $line .=
                " Total Price " . $this->formatPartAmount($qtyNum * $unitPrice);
        }

        $rcvWhDates = $extras["rcv_wh_dates"] ?? [];
        if (is_array($rcvWhDates) && $rcvWhDates !== []) {
            $line .= " | Rcv WH/GA: " . implode(" | ", $rcvWhDates);
        }

        $rcvToolDates = $extras["rcv_tool_dates"] ?? [];
        if (is_array($rcvToolDates) && $rcvToolDates !== []) {
            $line .= " | Rcv Tool Room: " . implode(" | ", $rcvToolDates);
        }

        $poNos = $extras["po_nos"] ?? [];
        if (is_array($poNos) && $poNos !== []) {
            $line .= " | PO: " . implode(" | ", $poNos);
        }

        $soEntries = $extras["so_entries"] ?? [];
        if (is_array($soEntries) && $soEntries !== []) {
            $soLabels = [];
            foreach ($soEntries as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $label = $this->formatSoEntry($entry);
                if ($label !== "") {
                    $soLabels[] = $label;
                }
            }
            if ($soLabels !== []) {
                $line .= " | SO: " . implode(" | ", $soLabels);
            }
        }

        return $line;
    }

    private function parsePartNumber(mixed $raw): ?float
    {
        $raw = trim((string) $raw);
        if ($raw === "") {
            return null;
        }

        if (preg_match('/^\d{1,3}(\.\d{3})*(,\d+)?$/', $raw)) {
            $normalized = str_replace(".", "", $raw);
            $normalized = str_replace(",", ".", $normalized);
        } else {
            $normalized = str_replace(",", ".", $raw);
        }

        if (!is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function formatPartAmount(float $amount): string
    {
        return number_format($amount, 0, ",", ".");
    }

    /**
     * @param array<string, string> $replacements
     */
    private function renderTemplate(
        string $template,
        array $replacements,
        bool $appendFooter,
    ): string {
        $message = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template,
        );

        if ($appendFooter) {
            $footer = (string) ($this->defaults["message_footer"] ?? "");
            if ($footer !== "") {
                $message .= str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $footer,
                );
            }
        }

        return $message;
    }

    private function formLink(string $formNo): string
    {
        $base = env_value("APP_MONITORING_URL");
        if ($base === false || trim((string) $base) === "") {
            $base = self::DEFAULT_MONITORING_URL;
        }

        $base = rtrim((string) $base, "/") . "/";

        if ($formNo === "" || $formNo === "-") {
            return $base;
        }

        return $base . "?form_no=" . rawurlencode($formNo);
    }

    private function shouldNotifyServicemanInfo(): bool
    {
        $envFlag = env_value("NOTIFY_SERVICEMAN_ALWAYS");
        if ($envFlag !== false && $envFlag !== "") {
            return $envFlag === "1" || strtolower((string) $envFlag) === "true";
        }

        $flag = $this->defaults["always_notify_serviceman"] ?? true;

        return $flag === true || $flag === 1 || $flag === "1";
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, string> $replacements
     * @param array<string, mixed> $form
     */
    private function notifyActionRecipients(
        array $rule,
        array $replacements,
        string $formServName,
        array $form,
        string $milestone,
    ): void {
        $actionRecipients = $rule["recipients"] ?? [];
        if ($actionRecipients === []) {
            return;
        }

        $actionMessage = $this->renderTemplate(
            (string) $rule["template"],
            $replacements,
            true,
        );
        $phones = $this->resolveRecipients($actionRecipients, $formServName);
        $this->sendToPhones($phones, $actionMessage, $form, $milestone);
    }

    /**
     * Serviceman (form_serv_name) selalu dapat notifikasi info di setiap milestone.
     *
     * @param array<string, string> $replacements
     * @param array<string, mixed> $form
     */
    private function notifyServicemanOnAllMilestones(
        array $replacements,
        string $formServName,
        array $form,
        string $milestone,
    ): void {
        if (!$this->shouldNotifyServicemanInfo()) {
            return;
        }

        $template =
            (string) ($this->defaults["serviceman_info_template"] ?? "");
        if ($template === "") {
            return;
        }

        if (trim($formServName) === "") {
            error_log(
                "FormMilestoneNotifier: serviceman skipped (empty form_serv_name) form=" .
                    ($form["id_form"] ?? "") .
                    " milestone=$milestone",
            );
            return;
        }

        $servicemanPhone = $this->servicemanPhone($formServName);
        if ($servicemanPhone === null) {
            error_log(
                "FormMilestoneNotifier: serviceman phone not found name=" .
                    $formServName .
                    " form=" .
                    ($form["id_form"] ?? "") .
                    " milestone=$milestone",
            );
            return;
        }

        $infoMessage = $this->renderTemplate($template, $replacements, false);
        $this->sendToPhones(
            [$servicemanPhone],
            $infoMessage,
            $form,
            $milestone,
        );
    }

    /**
     * @param list<string> $phones
     * @param array<string, mixed> $form
     */
    private function sendToPhones(
        array $phones,
        string $message,
        array $form,
        string $milestone,
    ): void {
        foreach (array_values(array_unique($phones)) as $phone) {
            $result = $this->client->sendText($phone, $message);
            if (!$result["ok"]) {
                error_log(
                    "FormMilestoneNotifier: send failed form=" .
                        ($form["id_form"] ?? "") .
                        " milestone=$milestone phone=$phone error=" .
                        $result["error"],
                );
            }
        }
    }

    /**
     * @param list<string> $recipientRules
     * @return list<string>
     */
    private function resolveRecipients(
        array $recipientRules,
        string $formServName,
    ): array {
        $phones = [];
        $servicemanCtx = null;

        foreach ($recipientRules as $rule) {
            if ($rule === "serviceman") {
                $phone = $this->servicemanPhone($formServName);
                if ($phone !== null) {
                    $phones[] = $phone;
                }
                continue;
            }

            if ($rule === "superior") {
                if ($servicemanCtx === null) {
                    $servicemanCtx = $this->servicemanContext($formServName);
                }
                $phone = WhacenterClient::normalizePhone(
                    $servicemanCtx?->no_telp_superior ?? null,
                );
                if ($phone !== null) {
                    $phones[] = $phone;
                }
                continue;
            }

            if (str_starts_with($rule, "role:")) {
                $phones = array_merge(
                    $phones,
                    $this->phonesForRole(substr($rule, 5)),
                );
                continue;
            }

            if (str_starts_with($rule, "level:")) {
                $phones = array_merge(
                    $phones,
                    $this->data->notify_phones_by_level(substr($rule, 6)),
                );
            }
        }

        return array_values(array_unique($phones));
    }

    private function servicemanPhone(string $formServName): ?string
    {
        $ctx = $this->servicemanContext($formServName);

        return WhacenterClient::normalizePhone($ctx?->no_telp ?? null);
    }

    private function servicemanContext(string $formServName): ?object
    {
        return $this->data->notify_context_by_nama_user($formServName);
    }

    /**
     * @return list<string>
     */
    private function phonesForRole(string $role): array
    {
        $phones = [];
        foreach ($this->levelRoles->levelsForRole($role) as $level) {
            $phones = array_merge(
                $phones,
                $this->data->notify_phones_by_level($level),
            );
        }

        return $phones;
    }

    private function roleDisplayName(string $role): string
    {
        $names = [];
        foreach ($this->levelRoles->levelsForRole($role) as $level) {
            $names = array_merge(
                $names,
                $this->data->notify_names_by_level($level),
            );
        }

        $names = array_values(array_unique($names));

        return $names !== [] ? implode(", ", $names) : "";
    }
}

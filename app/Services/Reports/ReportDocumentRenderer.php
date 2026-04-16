<?php

namespace App\Services\Reports;

use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\RequestRecord;
use App\Models\Role;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;

final class ReportDocumentRenderer
{
    public function renderHtml(RequestRecord $record): string
    {
        $record->loadMissing('layout', 'author');
        $layout = $record->layout;
        $author = $record->author;
        if ($layout === null || $author === null) {
            throw new \InvalidArgumentException('Заявка должна иметь макет и автора.');
        }

        $schema = $layout->schema ?? [];
        $data = $record->data ?? [];

        $recipient = $this->resolveRecipient($data) ?? $author;
        $placeholderMap = $this->buildPlaceholderMap($data, $author, $recipient);

        $headerHtml = '';
        if ($layout->has_header) {
            $headerConfig = (isset($schema['header']) && is_array($schema['header'])) ? $schema['header'] : null;
            $headerHtml = $this->renderHeaderBlocks($placeholderMap, $headerConfig, $data);
        }

        $bodyHtml = (string) ($schema['body_html'] ?? '');
        // Редактор сохраняет preview-шрифт (Times New Roman / Arial); DomPDF рендерит PDF только через DejaVu.
        // Встроенный font-family без файла шрифта → подстановка без кириллицы («?????» у текста в теле).
        $bodyHtml = $this->stripInlineFontFamiliesForPdf($bodyHtml);
        $bodyHtml = $this->replaceWriteoffEquipmentListToken($bodyHtml);
        $bodyHtml = $this->replaceMoveEquipmentListToken($bodyHtml);
        $bodyHtml = $this->replaceTableTokens($bodyHtml, $data);
        $bodyHtml = $this->replacePlaceholders($bodyHtml, $placeholderMap);

        $title = array_key_exists('document_title', $schema)
            ? trim((string) $schema['document_title'])
            : 'Документ';
        $subtitle = array_key_exists('document_subtitle', $schema)
            ? trim((string) $schema['document_subtitle'])
            : '';

        $titleFontPt = $this->clampFontSizePt($schema['document_title_font_size_pt'] ?? 16);
        $subtitleFontPt = $this->clampFontSizePt($schema['document_subtitle_font_size_pt'] ?? 12);
        $bodyFontStack = $this->normalizePdfFontFamily($schema['body_default_font_family'] ?? null);
        $bodyFontPt = $this->clampFontSizePt($schema['body_default_font_size_pt'] ?? 11);
        $bodyLineHeight = $this->clampLineHeight($schema['body_line_height'] ?? 1.35);

        $footerBlock = $this->buildFooterBlock($schema, $placeholderMap, $record);

        return Blade::render(<<<'BLADE'
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 16mm; }
        body { font-family: {!! $bodyFontStack !!}; font-size: {{ $bodyFontPt }}pt; line-height: {{ $bodyLineHeight }}; color: #111; }
        .header-block { margin-bottom: 14px; }
        .header-block.text-right { text-align: right; }
        .header-block.text-center { text-align: center; }
        .doc-title { text-align: center; font-weight: bold; font-size: {{ $titleFontPt }}pt; font-family: {!! $bodyFontStack !!}; margin: 18px 0 6px; }
        .doc-subtitle { text-align: center; font-size: {{ $subtitleFontPt }}pt; font-family: {!! $bodyFontStack !!}; margin-bottom: 16px; }
        .body-flow { font-family: {!! $bodyFontStack !!}; font-size: {{ $bodyFontPt }}pt; line-height: {{ $bodyLineHeight }}; }
        .body-flow p { margin: 0 0 8px; }
        table.data-table { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: {{ $bodyFontPt }}pt; }
        table.data-table th, table.data-table td { border: 1px solid #333; padding: 6px 8px; vertical-align: top; }
        table.data-table th { background: #f0f0f0; font-weight: bold; }
        .footer-row { margin-top: 28px; display: table; width: 100%; font-size: {{ $bodyFontPt }}pt; }
        .footer-left, .footer-right { display: table-cell; width: 50%; vertical-align: bottom; }
        .footer-right { text-align: right; }
        .sign-line { border-top: 1px solid #000; display: inline-block; min-width: 160px; margin-right: 6px; }
        .footer-triple { margin-top: 28px; font-size: {{ $bodyFontPt }}pt; }
        .footer-triple .footer-date-line { margin-bottom: 14px; }
        table.sig-line { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.sig-line td { vertical-align: bottom; padding: 2px 0; font-size: {{ $bodyFontPt }}pt; }
        table.sig-line td.sig-role { white-space: nowrap; padding-right: 6px; }
        table.sig-line td.sig-dots { border-bottom: 1px dotted #333; }
        table.sig-line td.sig-name { white-space: nowrap; padding-left: 8px; text-align: right; }
    </style>
</head>
<body>
    {!! $headerHtml !!}
    @if($title !== '')
        <div class="doc-title">{{ $title }}</div>
    @endif
    @if($subtitle !== '')
        <div class="doc-subtitle">{{ $subtitle }}</div>
    @endif
    <div class="body-flow">{!! $bodyHtml !!}</div>
    {!! $footerBlock !!}
</body>
</html>
BLADE, [
            'headerHtml' => $headerHtml,
            'title' => $title,
            'subtitle' => $subtitle,
            'bodyHtml' => $bodyHtml,
            'footerBlock' => $footerBlock,
            'bodyFontStack' => $bodyFontStack,
            'bodyFontPt' => $bodyFontPt,
            'bodyLineHeight' => $bodyLineHeight,
            'titleFontPt' => $titleFontPt,
            'subtitleFontPt' => $subtitleFontPt,
        ]);
    }

    public function renderPdfResponse(RequestRecord $record, ?string $filename = null)
    {
        $html = $this->renderHtml($record);
        $name = $filename ?? ('zayavka-'.$record->id.'.pdf');

        return Pdf::loadHTML($html, 'UTF-8')
            ->setPaper('a4', 'portrait')
            ->download($name);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, string>  $placeholderMap
     */
    private function buildFooterBlock(array $schema, array $placeholderMap, RequestRecord $record): string
    {
        $dateRaw = $placeholderMap['sys.date'] ?? now()->format('d.m.Y');
        $dateEsc = e((string) $dateRaw);

        $author = $record->author;
        if ($author === null) {
            throw new \InvalidArgumentException('Заявка должна иметь автора.');
        }

        $pdfFooter = (isset($schema['pdf_footer']) && is_array($schema['pdf_footer'])) ? $schema['pdf_footer'] : [];
        $style = $this->normalizePdfFooterStyle((string) ($pdfFooter['style'] ?? 'legacy'));

        if ($style === 'rapport_two') {
            return $this->buildRapportSignatureFooterHtml($dateEsc, $record, 2, $pdfFooter);
        }
        if ($style === 'rapport_three') {
            return $this->buildRapportSignatureFooterHtml($dateEsc, $record, 3, $pdfFooter);
        }

        $signName = e((string) $author->name);

        return '<div class="footer-row"><div class="footer-left">'.$dateEsc.'</div>'
            .'<div class="footer-right"><span class="sign-line"></span> / '.$signName.'</div></div>';
    }

    /**
     * Старые макеты хранили два подписанта как style = triple.
     */
    private function normalizePdfFooterStyle(string $style): string
    {
        if ($style === 'triple') {
            return 'rapport_two';
        }
        if ($style === 'rapport_two' || $style === 'rapport_three') {
            return $style;
        }

        return 'legacy';
    }

    private function firstActiveUserByRole(string $roleName): ?User
    {
        $roleId = Role::query()->where('name', $roleName)->value('id');
        if ($roleId === null) {
            return null;
        }

        return User::query()
            ->where('is_active', true)
            ->where('role_id', (int) $roleId)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('patronymic')
            ->first();
    }

    /**
     * @param  2|3  $signerCount
     * @param  array<string, mixed>  $pdfFooter
     */
    private function buildRapportSignatureFooterHtml(string $dateEscaped, RequestRecord $record, int $signerCount, array $pdfFooter): string
    {
        $head = $this->resolveFooterUserByPickedId($pdfFooter['head_user_id'] ?? null, 'user');
        $senior = $this->resolveSeniorNurseSignerForFooter($record);
        $engineer = $signerCount >= 3
            ? $this->resolveFooterUserByPickedId($pdfFooter['engineer_user_id'] ?? null, 'admin')
            : null;

        $rows = [
            ['Заведующая отделением', $head],
            ['Старшая медсестра', $senior],
        ];
        if ($signerCount >= 3) {
            $rows[] = ['Инженер', $engineer];
        }

        $html = '<div class="footer-triple"><div class="footer-date-line">'.$dateEscaped.'</div>';

        foreach ($rows as [$label, $signer]) {
            $nameHtml = $signer !== null ? e((string) $signer->name) : '________________';
            $html .= '<table class="sig-line"><tr>'
                .'<td class="sig-role">'.$this->safeText($label).'</td>'
                .'<td class="sig-dots">&nbsp;</td>'
                .'<td class="sig-name">'.$nameHtml.'</td>'
                .'</tr></table>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Подписант по id из макета; при пустом или неверном id — первый активный с нужной ролью.
     *
     * @param  mixed  $rawId
     */
    private function resolveFooterUserByPickedId(mixed $rawId, string $expectedRole): ?User
    {
        $id = is_numeric($rawId) ? (int) $rawId : 0;
        if ($id > 0) {
            $user = User::query()->where('is_active', true)->whereKey($id)->first();
            if ($user !== null && $user->role === $expectedRole) {
                return $user;
            }
        }

        return $this->firstActiveUserByRole($expectedRole);
    }

    /**
     * Старшая медсестра в подвале: автор последней неподтверждённой заявки на списание или перемещение;
     * иначе автор PDF-заявки (если роль senior_nurse); иначе первая активная старшая медсестра из БД.
     */
    private function resolveSeniorNurseSignerForFooter(RequestRecord $record): ?User
    {
        $fromRequest = $this->userFromLatestPendingWriteoffOrMoveRequest();

        if ($fromRequest !== null) {
            return $fromRequest;
        }

        $author = $record->author;
        if ($author !== null && $author->is_active && $author->role === 'senior_nurse') {
            return $author;
        }

        return $this->firstActiveUserByRole('senior_nurse');
    }

    private function userFromLatestPendingWriteoffOrMoveRequest(): ?User
    {
        $equipmentRequest = EquipmentRequest::query()
            ->whereRelation('requestStatus', 'code', EquipmentRequest::STATUS_PENDING)
            ->where(function ($q): void {
                $q->whereRelation('requestType', 'code', EquipmentRequest::TYPE_WRITEOFF)
                    ->orWhereRelation('requestType', 'code', EquipmentRequest::TYPE_MOVE);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($equipmentRequest === null) {
            return null;
        }

        return User::query()
            ->where('is_active', true)
            ->whereKey($equipmentRequest->user_id)
            ->first();
    }

    private function resolveRecipient(array $data): ?User
    {
        $id = $data['recipient_user_id'] ?? null;
        if ($id === null || $id === '') {
            return null;
        }

        return User::query()->find((int) $id);
    }

    /**
     * Сырые строки для подстановки; экранирование в {@see replacePlaceholders}.
     *
     * @return array<string, string>
     */
    private function buildPlaceholderMap(array $data, User $author, User $recipient): array
    {
        $map = [
            'sys.date' => now()->format('d.m.Y'),
            'user.name' => (string) $author->name,
            'user.id' => (string) $author->getKey(),
            'user.username' => (string) ($author->username ?? ''),
            'user.role_label' => (string) ($author->role_label ?? ''),
            'recipient.name' => (string) $recipient->name,
            'recipient.id' => (string) $recipient->getKey(),
            'recipient.username' => (string) ($recipient->username ?? ''),
            'recipient.role_label' => (string) ($recipient->role_label ?? ''),
        ];

        foreach ($data as $key => $value) {
            if (! is_string($key)) {
                continue;
            }
            if (str_starts_with($key, '_')) {
                continue;
            }
            if (is_array($value)) {
                continue;
            }
            $map['field:'.$key] = is_scalar($value) ? (string) $value : '';
        }

        return $map;
    }

    private function safeText(string $value): string
    {
        return e($value);
    }

    /**
     * Шапка в JSON {@see RequestLayout} → schema.header: приоритет у {@see sections} (до трёх блоков), иначе legacy {@see blocks}.
     *
     * @param  array<string, string>  $map
     * @param  array<string, mixed>  $recordData  сырые data заявки (в т.ч. header_overrides по line_id)
     * @param  array<string, mixed>|null  $headerRoot
     */
    private function renderHeaderBlocks(array $map, ?array $headerRoot, array $recordData = []): string
    {
        if ($headerRoot === null) {
            return '';
        }

        $headerOverrides = is_array($recordData['header_overrides'] ?? null) ? $recordData['header_overrides'] : [];

        if (isset($headerRoot['sections']) && is_array($headerRoot['sections']) && $headerRoot['sections'] !== []) {
            return $this->renderHeaderSections($map, $headerRoot['sections'], $headerOverrides);
        }

        if (! isset($headerRoot['blocks']) || ! is_array($headerRoot['blocks']) || $headerRoot['blocks'] === []) {
            return '';
        }

        $out = '';
        foreach ($headerRoot['blocks'] as $block) {
            if (! is_array($block)) {
                continue;
            }
            $align = match ($block['align'] ?? 'left') {
                'right' => 'text-right',
                'center' => 'text-center',
                default => 'text-left',
            };
            $inner = '';
            if (isset($block['html'])) {
                $inner = $this->replacePlaceholders((string) $block['html'], $map);
            } elseif (isset($block['lines']) && is_array($block['lines'])) {
                $lines = [];
                foreach ($block['lines'] as $line) {
                    $lines[] = '<div>'.$this->replacePlaceholders((string) $line, $map).'</div>';
                }
                $inner = implode('', $lines);
            } elseif (isset($block['text'])) {
                $inner = '<div>'.$this->replacePlaceholders((string) $block['text'], $map).'</div>';
            }
            if ($inner !== '') {
                $fontStack = $this->normalizePdfFontFamily(null);
                $fontPt = $this->clampFontSizePt(12);
                $out .= '<div class="header-block '.$align.'" style="font-family:'.$fontStack.';font-size:'.$fontPt.'pt;margin-bottom:16px;">'.$inner.'</div>';
            }
        }

        return $out;
    }

    /**
     * @param  array<int, mixed>  $sections
     * @param  array<string, mixed>  $headerOverrides  line_id => текст из заявки
     */
    private function renderHeaderSections(array $map, array $sections, array $headerOverrides = []): string
    {
        $out = '';
        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }
            $lines = $section['lines'] ?? [];
            if (! is_array($lines) || $lines === []) {
                continue;
            }
            $align = match ($section['align'] ?? 'center') {
                'right' => 'text-right',
                'left' => 'text-left',
                default => 'text-center',
            };
            $weight = ($section['bold'] ?? true) ? 'font-weight:bold;' : '';
            $fontStack = $this->normalizePdfFontFamily($section['font_family'] ?? null);
            $fontPt = $this->clampFontSizePt($section['font_size_pt'] ?? 12);
            $inner = '';
            foreach ($lines as $line) {
                $raw = $this->resolveHeaderLineText($line, $headerOverrides);
                $inner .= '<div style="margin-bottom:3px;">'.$this->replacePlaceholders($raw, $map).'</div>';
            }
            if ($inner !== '') {
                $out .= '<div class="header-block '.$align.'" style="'.$weight.'font-family:'.$fontStack.';font-size:'.$fontPt.'pt;margin-bottom:16px;">'.$inner.'</div>';
            }
        }

        return $out;
    }

    /**
     * Строка шапки: строка JSON или { text, editable, line_id } с подстановкой из заявки.
     *
     * @param  mixed  $line
     */
    private function resolveHeaderLineText(mixed $line, array $headerOverrides): string
    {
        if (is_string($line) || is_numeric($line)) {
            return (string) $line;
        }
        if (! is_array($line)) {
            return '';
        }
        $base = (string) ($line['text'] ?? '');
        if (! empty($line['editable']) && ! empty($line['line_id'])) {
            $id = (string) $line['line_id'];
            if (array_key_exists($id, $headerOverrides)) {
                return (string) $headerOverrides[$id];
            }
        }

        return $base;
    }

    private function normalizePdfFontFamily(mixed $value): string
    {
        if ($value === 'DejaVu Sans') {
            return 'DejaVu Sans, sans-serif';
        }

        return 'DejaVu Serif, serif';
    }

    private function clampFontSizePt(mixed $value): int
    {
        $n = is_numeric($value) ? (int) round((float) $value) : 11;

        return max(6, min(28, $n));
    }

    private function clampLineHeight(mixed $value): float
    {
        $n = is_numeric($value) ? (float) $value : 1.35;

        return max(1.0, min(2.5, round($n, 2)));
    }

    /**
     * @param  array<string, string>  $map
     */
    private function replacePlaceholders(string $html, array $map): string
    {
        return preg_replace_callback('/\{\{\s*([^}]+?)\s*\}\}/', function (array $m) use ($map) {
            $key = trim($m[1]);
            $raw = $map[$key] ?? '';
            $str = is_string($raw) ? $raw : '';

            if (str_starts_with($key, 'field:')) {
                return $this->sanitizeFieldHtml($str);
            }

            return e($str);
        }, $html) ?? $html;
    }

    /**
     * Подмножество HTML из WYSIWYG полей заявки (безопасная вставка в PDF).
     */
    private function sanitizeFieldHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '' || $html === '<p><br></p>' || $html === '<p></p>') {
            return '';
        }
        if (! preg_match('/<[a-zA-Z]/', $html)) {
            return e($html);
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="UTF-8"><div id="__f">'.str_replace("\0", '', $html).'</div>';
        if (! @$dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            return e(strip_tags($html));
        }
        libxml_clear_errors();

        $root = $dom->getElementById('__f');
        if (! $root instanceof \DOMElement) {
            return e(strip_tags($html));
        }

        $this->sanitizeRichFieldElement($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    private function sanitizeRichFieldElement(\DOMElement $node): void
    {
        $allowed = ['p', 'div', 'span', 'br', 'strong', 'b', 'em', 'i', 'u', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

        $child = $node->firstChild;
        while ($child !== null) {
            $next = $child->nextSibling;
            if ($child instanceof \DOMText) {
                $child = $next;

                continue;
            }
            if (! $child instanceof \DOMElement) {
                $node->removeChild($child);
                $child = $next;

                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'form', 'input', 'button'], true)) {
                $node->removeChild($child);
                $child = $next;

                continue;
            }
            if (! in_array($tag, $allowed, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                $child = $next;

                continue;
            }

            if ($child->hasAttributes()) {
                $styleVal = null;
                if ($child->hasAttribute('style')) {
                    $styleVal = $child->getAttribute('style');
                }
                while ($child->attributes->length > 0) {
                    $child->removeAttributeNode($child->attributes->item(0));
                }
                if (in_array($tag, ['span', 'div', 'p'], true) && $styleVal !== null && $styleVal !== '') {
                    $safe = $this->sanitizeInlineStyleForField($styleVal);
                    if ($safe !== '') {
                        $child->setAttribute('style', $safe);
                    }
                }
            }

            $this->sanitizeRichFieldElement($child);
            $child = $next;
        }
    }

    private function sanitizeInlineStyleForField(string $style): string
    {
        $parts = preg_split('/;/', $style) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || ! str_contains($part, ':')) {
                continue;
            }
            [$prop, $val] = array_map('trim', explode(':', $part, 2));
            $prop = strtolower($prop);
            // Не сохраняем font-family: в PDF только DejaVu; Times/Arial из редактора ломают кириллицу в DomPDF.
            if (! in_array($prop, ['font-size', 'font-weight', 'text-align', 'text-decoration', 'line-height'], true)) {
                continue;
            }
            $valLower = strtolower($val);
            if (preg_match('/url\s*\(|expression\s*\(|javascript:|@import/i', $valLower)) {
                continue;
            }
            if (strlen($val) > 120) {
                continue;
            }
            $out[] = $prop.':'.$val;
        }

        return implode(';', $out);
    }

    private function replaceTableTokens(string $html, array $data): string
    {
        return preg_replace_callback('/\{\{\s*table:([\w.-]+)\s*\}\}/', function (array $m) use ($data) {
            $key = $m[1];
            $rows = $data[$key] ?? null;
            if (! is_array($rows) || $rows === []) {
                return '<table class="data-table"><tbody><tr><td>—</td></tr></tbody></table>';
            }
            $first = $rows[0] ?? [];
            if (! is_array($first)) {
                return '<table class="data-table"><tbody><tr><td>—</td></tr></tbody></table>';
            }
            $headers = array_keys($first);
            $thead = '<thead><tr>';
            foreach ($headers as $h) {
                $thead .= '<th>'.$this->safeText((string) $h).'</th>';
            }
            $thead .= '</tr></thead>';
            $tbody = '<tbody>';
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $tbody .= '<tr>';
                foreach ($headers as $h) {
                    $cell = $row[$h] ?? '';
                    $tbody .= '<td>'.$this->safeText(is_scalar($cell) ? (string) $cell : '').'</td>';
                }
                $tbody .= '</tr>';
            }
            $tbody .= '</tbody>';

            return '<table class="data-table">'.$thead.$tbody.'</table>';
        }, $html) ?? $html;
    }

    private function replaceWriteoffEquipmentListToken(string $html): string
    {
        if (! preg_match('/\{\{\s*sys\.writeoff_equipment_list\s*\}\}/', $html)) {
            return $html;
        }

        // Только позиции, по которым подана заявка на списание и администратор ещё не подтвердил (writeoff_states.code = requested).
        $items = Equipment::query()
            ->with(['writeoffState'])
            ->whereHas('writeoffState', fn ($q) => $q->where('code', 'requested'))
            ->orderBy('number')
            ->get(['id', 'number', 'name', 'inventory_number', 'serial_number', 'writeoff_state_id']);

        if ($items->isEmpty()) {
            $empty = '<p>Нет оборудования, ожидающего подтверждения списания администратором.</p>';

            return preg_replace('/\{\{\s*sys\.writeoff_equipment_list\s*\}\}/', $empty, $html) ?? $html;
        }

        $list = '<ol>';
        foreach ($items as $item) {
            $parts = [
                trim((string) $item->name),
            ];
            if (! empty($item->inventory_number)) {
                $parts[] = 'инв. № '.$item->inventory_number;
            }
            if (! empty($item->serial_number)) {
                $parts[] = 'сер. № '.$item->serial_number;
            }
            $line = implode(', ', array_filter($parts, fn ($x) => $x !== ''));
            $list .= '<li>'.$this->safeText($line).'</li>';
        }
        $list .= '</ol>';

        return preg_replace('/\{\{\s*sys\.writeoff_equipment_list\s*\}\}/', $list, $html) ?? $html;
    }

    private function replaceMoveEquipmentListToken(string $html): string
    {
        if (! preg_match('/\{\{\s*sys\.move_equipment_list\s*\}\}/', $html)) {
            return $html;
        }

        // Оборудование с заявкой на перемещение (equipment_requests: type = move), пока администратор не подтвердил (pending).
        $items = Equipment::query()
            ->whereHas('requests', function ($q): void {
                $q->whereRelation('requestType', 'code', EquipmentRequest::TYPE_MOVE)
                    ->whereRelation('requestStatus', 'code', EquipmentRequest::STATUS_PENDING);
            })
            ->orderBy('number')
            ->get([
                'id',
                'number',
                'name',
                'inventory_number',
                'serial_number',
                'production_date',
                'year_of_manufacture',
            ]);

        if ($items->isEmpty()) {
            $empty = '<p>Нет оборудования с неподтверждённой администратором заявкой на перемещение.</p>';

            return preg_replace('/\{\{\s*sys\.move_equipment_list\s*\}\}/', $empty, $html) ?? $html;
        }

        $list = '<ol>';
        foreach ($items as $item) {
            $segments = [trim((string) $item->name)];
            $inv = trim((string) ($item->inventory_number ?? ''));
            if ($inv !== '') {
                $segments[] = 'инвентарный номер '.$inv;
            }
            $serial = trim((string) ($item->serial_number ?? ''));
            if ($serial !== '') {
                $segments[] = 'заводской номер '.$serial;
            }
            $datePart = '';
            if ($item->production_date !== null) {
                $datePart = $item->production_date->format('d.m.Y');
            } elseif ($item->year_of_manufacture !== null && (string) $item->year_of_manufacture !== '') {
                $datePart = (string) $item->year_of_manufacture;
            }
            if ($datePart !== '') {
                $segments[] = 'дата выпуска '.$datePart.'г';
            }
            $line = implode(', ', array_filter($segments, fn ($x) => $x !== ''));
            $list .= '<li>'.$this->safeText($line).'</li>';
        }
        $list .= '</ol>';

        return preg_replace('/\{\{\s*sys\.move_equipment_list\s*\}\}/', $list, $html) ?? $html;
    }

    /**
     * Убирает inline font-family из HTML тела макета, чтобы наследовался шрифт .body-flow (DejaVu) и корректно шла кириллица.
     */
    private function stripInlineFontFamiliesForPdf(string $html): string
    {
        if ($html === '') {
            return $html;
        }
        if (stripos($html, 'font-family') === false) {
            return $html;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="UTF-8"><div id="__pdf_body">'.str_replace("\0", '', $html).'</div>';
        if (! @$dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            libxml_clear_errors();

            return $this->stripFontFamilyAttributesRegexFallback($html);
        }
        libxml_clear_errors();

        $root = $dom->getElementById('__pdf_body');
        if (! $root instanceof \DOMElement) {
            return $this->stripFontFamilyAttributesRegexFallback($html);
        }

        $this->stripInlineFontFamilyOnElementTree($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    private function stripInlineFontFamilyOnElementTree(\DOMElement $node): void
    {
        if ($node->hasAttribute('style')) {
            $cleaned = $this->removeFontFamilyFromInlineStyle($node->getAttribute('style'));
            if ($cleaned === '') {
                $node->removeAttribute('style');
            } else {
                $node->setAttribute('style', $cleaned);
            }
        }

        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $this->stripInlineFontFamilyOnElementTree($child);
            }
        }
    }

    private function removeFontFamilyFromInlineStyle(string $style): string
    {
        $parts = preg_split('/;/', $style) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || ! str_contains($part, ':')) {
                continue;
            }
            [$prop, $val] = array_map('trim', explode(':', $part, 2));
            if (strtolower($prop) === 'font-family') {
                continue;
            }
            $out[] = $prop.':'.$val;
        }

        return implode(';', $out);
    }

    private function stripFontFamilyAttributesRegexFallback(string $html): string
    {
        return preg_replace_callback(
            '/\sstyle\s*=\s*(")([^"]*)(")|\sstyle\s*=\s*(\')([^\']*)(\')/i',
            function (array $m): string {
                $inner = $m[1] !== '' ? $m[2] : $m[5];
                $q = $m[1] !== '' ? $m[1] : $m[4];
                $cleaned = $this->removeFontFamilyFromInlineStyle($inner);
                if ($cleaned === '') {
                    return '';
                }

                return ' style='.$q.$cleaned.$q;
            },
            $html
        ) ?? $html;
    }
}

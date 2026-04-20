<?php

namespace App\Services\Reports;

use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\RequestRecord;
use App\Models\Role;
use App\Models\User;
use App\Services\DadataContextualHtmlDeclineService;
use App\Services\DadataNameDeclensionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

final class ReportDocumentRenderer
{
    private const REPORT_KIND_WRITEOFF = 'writeoff';

    private const REPORT_KIND_MOVE = 'move';

    private const REPORT_KIND_DEFAULT = 'default';

    /** @var list<string> */
    private const PLACEHOLDER_PERSON_NAME_KEYS = [
        'user.name',
        'recipient.name',
        'sys.chief_doctor_name',
        'sys.department_head_name',
        'sys.senior_nurse_name',
    ];

    /** Роли, допустимые для явного выбора «заведующая отделением» в макете PDF. */
    private const RAPPORT_HEAD_LAYOUT_PICK_ROLES = ['user', 'sign_writeoff_head', 'sign_move_head'];

    public function __construct(
        private readonly DadataNameDeclensionService $dadataNames,
        private readonly DadataContextualHtmlDeclineService $contextualHtmlDecline,
    ) {}

    public function renderHtml(RequestRecord $record): string
    {
        $record->loadMissing(['layout.documentHeader', 'author']);
        $layout = $record->layout;
        $author = $record->author;
        if ($layout === null || $author === null) {
            throw new \InvalidArgumentException('Заявка должна иметь макет и автора.');
        }

        $schema = $layout->effectiveSchema();
        $data = $record->data ?? [];

        $recipient = $this->resolveRecipient($data) ?? $author;
        $reportKind = $this->detectReportKind($layout->title ?? null, $schema);
        $placeholderMap = $this->buildPlaceholderMap($data, $author, $recipient, $record, $reportKind);

        $headerHtml = '';
        if ($layout->has_header) {
            $headerConfig = (isset($schema['header']) && is_array($schema['header'])) ? $schema['header'] : null;
            $headerHtml = $this->renderHeaderBlocks($placeholderMap, $headerConfig, $data, $reportKind);
        }

        $bodyHtml = (string) ($schema['body_html'] ?? '');
        // Редактор сохраняет preview-шрифт (Times New Roman / Arial); DomPDF рендерит PDF только через DejaVu.
        // Встроенный font-family без файла шрифта → подстановка без кириллицы («?????» у текста в теле).
        $bodyHtml = $this->stripInlineFontFamiliesForPdf($bodyHtml);
        $bodyHtml = $this->replaceWriteoffEquipmentListToken($bodyHtml);
        $bodyHtml = $this->replaceMoveEquipmentListToken($bodyHtml);
        $bodyHtml = $this->replaceTableTokens($bodyHtml, $data);
        $bodyHtml = $this->replacePlaceholders($bodyHtml, $placeholderMap);

        $contextDeclineCandidates = $this->collectPersonNameCandidatesForContextDecline($placeholderMap, $data, $schema);
        if ($contextDeclineCandidates !== []) {
            $headerHtml = $this->contextualHtmlDecline->applyToHtml($headerHtml, $contextDeclineCandidates);
            $bodyHtml = $this->contextualHtmlDecline->applyToHtml($bodyHtml, $contextDeclineCandidates);
        }

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

        $footerBlock = $this->buildFooterBlock($schema, $placeholderMap, $record, $reportKind);

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
        table.sig-line { width: 100%; border-collapse: collapse; margin: 12px 0; table-layout: fixed; }
        table.sig-line td { vertical-align: bottom; padding: 2px 0; font-size: {{ $bodyFontPt }}pt; }
        table.sig-line td.sig-role { white-space: nowrap; width: 1%; padding-right: 16px; }
        table.sig-line td.sig-name { text-align: right; word-wrap: break-word; }
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
        $name = $filename ?? $this->defaultPdfDownloadFilename($record);

        return Pdf::loadHTML($html, 'UTF-8')
            ->setPaper('a4', 'portrait')
            ->download($name);
    }

    /**
     * Имя файла при скачивании: название макета ({@see RequestLayout::$title}) и значения полей заявки
     * (поля с флагом include_in_pdf_filename или эвристика по подписи поля).
     */
    private function defaultPdfDownloadFilename(RequestRecord $record): string
    {
        $record->loadMissing(['layout.documentHeader']);
        $layout = $record->layout;
        $schema = $layout !== null ? $layout->effectiveSchema() : [];

        $layoutTitle = trim((string) ($layout?->title ?? ''));
        if ($layoutTitle === '') {
            $layoutTitle = isset($schema['document_title']) ? trim((string) $schema['document_title']) : '';
        }
        if ($layoutTitle === '') {
            $layoutTitle = 'zayavka-'.$record->getKey();
        }

        $base = $this->sanitizePdfDownloadBasename($layoutTitle);
        if ($base === '') {
            $base = 'zayavka-'.$record->getKey();
        }

        $segments = $this->pdfFilenameSegmentsFromRequestData($record, $schema);
        $parts = [$base];
        foreach ($segments as $seg) {
            $clean = $this->sanitizePdfDownloadBasename($seg);
            if ($clean !== '') {
                $parts[] = $clean;
            }
        }

        if (count($parts) === 1) {
            $registry = (int) ($record->registry_number ?? 0);
            $parts[] = '№'.$registry;
        }

        $stem = implode('_', $parts);
        $stem = Str::limit($stem, 180, '');

        return $stem.'.pdf';
    }

    /**
     * Значения полей для имени файла: явные флаги в макете или эвристика по названию поля.
     *
     * @return list<string>
     */
    private function pdfFilenameSegmentsFromRequestData(RequestRecord $record, array $schema): array
    {
        $data = is_array($record->data) ? $record->data : [];
        $fields = isset($schema['fields']) && is_array($schema['fields']) ? $schema['fields'] : [];

        $hasExplicit = false;
        foreach ($fields as $f) {
            if (is_array($f) && ! empty($f['include_in_pdf_filename'])) {
                $hasExplicit = true;
                break;
            }
        }

        if ($hasExplicit) {
            $out = [];
            foreach ($fields as $f) {
                if (! is_array($f) || empty($f['include_in_pdf_filename'])) {
                    continue;
                }
                $id = (string) ($f['id'] ?? '');
                if ($id === '' || ! array_key_exists($id, $data)) {
                    continue;
                }
                $plain = $this->extractPlainTextForPdfFilename($data[$id]);
                if ($plain !== '') {
                    $out[] = $plain;
                }
            }

            return $out;
        }

        return $this->pdfFilenameSegmentsByLabelHeuristic($fields, $data);
    }

    /**
     * Старые макеты без флагов: одно поле «наименование оборудования» и одно «модель» / «тип» и т.п.
     *
     * @param  array<int, mixed>  $fields
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    private function pdfFilenameSegmentsByLabelHeuristic(array $fields, array $data): array
    {
        $out = [];

        foreach ($fields as $f) {
            if (! is_array($f)) {
                continue;
            }
            $id = (string) ($f['id'] ?? '');
            $label = mb_strtolower((string) ($f['name'] ?? ''), 'UTF-8');
            if ($id === '' || ! array_key_exists($id, $data)) {
                continue;
            }
            if (! preg_match('/наименование|название|оборудован/i', $label)) {
                continue;
            }
            if (preg_match('/^модель|^тип\s|марка|заводск/i', $label)) {
                continue;
            }
            $plain = $this->extractPlainTextForPdfFilename($data[$id]);
            if ($plain !== '') {
                $out[] = $plain;
                break;
            }
        }

        foreach ($fields as $f) {
            if (! is_array($f)) {
                continue;
            }
            $id = (string) ($f['id'] ?? '');
            $label = (string) ($f['name'] ?? '');
            if ($id === '' || ! array_key_exists($id, $data)) {
                continue;
            }
            if (! preg_match('/модель|тип|марка|заводск|гк-/iu', $label)) {
                continue;
            }
            if (preg_match('/наименование|название|оборудован/i', mb_strtolower($label, 'UTF-8'))
                && ! preg_match('/^модель|^тип\s/u', mb_strtolower($label, 'UTF-8'))) {
                continue;
            }
            $plain = $this->extractPlainTextForPdfFilename($data[$id]);
            if ($plain !== '') {
                $out[] = $plain;
                break;
            }
        }

        return $out;
    }

    private function extractPlainTextForPdfFilename(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return '';
        }
        if (is_numeric($value)) {
            return trim((string) $value);
        }
        $s = trim((string) $value);
        if ($s === '') {
            return '';
        }
        if (str_contains($s, '<') && preg_match('/<[a-z][\s\S]*>/i', $s)) {
            $s = strip_tags($s);
        }
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return trim($s);
    }

    private function sanitizePdfDownloadBasename(string $name): string
    {
        $name = basename(str_replace(["\0"], '', $name));
        $name = str_replace(['/', '\\'], '_', $name);
        $name = preg_replace('/[^\p{L}\p{N}\s._\-–—()«»№]/u', '_', $name) ?? '';
        $name = trim(preg_replace('/_+/u', '_', $name) ?? '', '._ ');

        return Str::limit($name, 120, '');
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, string>  $placeholderMap
     */
    private function buildFooterBlock(array $schema, array $placeholderMap, RequestRecord $record, string $reportKind): string
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
            return $this->buildRapportSignatureFooterHtml($dateEsc, $record, 2, $pdfFooter, $reportKind);
        }
        if ($style === 'rapport_three') {
            return $this->buildRapportSignatureFooterHtml($dateEsc, $record, 3, $pdfFooter, $reportKind);
        }

        $signName = $this->pdfPersonName((string) $author->name);

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
    private function buildRapportSignatureFooterHtml(string $dateEscaped, RequestRecord $record, int $signerCount, array $pdfFooter, string $reportKind): string
    {
        $head = $this->resolveRapportHeadFromLayoutPick($pdfFooter['head_user_id'] ?? null)
            ?? $this->resolveDepartmentHeadSignerForReport($reportKind)
            ?? $this->firstActiveUserByRole('user');
        $senior = $this->resolveSeniorNurseSignerForFooter($record, $reportKind);
        // Инженер в рапорте всегда из роли "admin" (администратор), без подстановки других ролей.
        $engineer = $signerCount >= 3
            ? $this->firstActiveUserByRole('admin')
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
            $nameHtml = $this->rapportFooterSignerNameHtml($signer);
            $html .= '<table class="sig-line"><tr>'
                .'<td class="sig-role">'.$this->safeText($label).'</td>'
                .'<td class="sig-name">'.$nameHtml.'</td>'
                .'</tr></table>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * ФИО в подписи рапорта — именительный падеж как в учётной записи, без DaData (подпись не «кому», а кто подписывает).
     */
    private function rapportFooterSignerNameHtml(?User $signer): string
    {
        if ($signer === null) {
            return '________________';
        }

        return e(trim((string) $signer->name));
    }

    /**
     * Заведующая отделением из макета: явный выбор id (роли «Пользователь» или подписант отделения по типу заявки).
     * Служебные подписанты (sign_*_head) могут быть без входа в систему (is_active = false).
     */
    private function resolveRapportHeadFromLayoutPick(mixed $rawId): ?User
    {
        $id = is_numeric($rawId) ? (int) $rawId : 0;
        if ($id <= 0) {
            return null;
        }
        $user = User::query()->whereKey($id)->first();
        if ($user === null) {
            return null;
        }
        if (! in_array($user->role, self::RAPPORT_HEAD_LAYOUT_PICK_ROLES, true)) {
            return null;
        }
        if (! $user->is_active && ! in_array($user->role, ['sign_writeoff_head', 'sign_move_head'], true)) {
            return null;
        }

        return $user;
    }

    /**
     * Старшая медсестра: в первую очередь автор текущей PDF-заявки (кто её заполнил),
     * если роль автора = senior_nurse; иначе автор последней неподтверждённой заявки нужного типа;
     * иначе первая активная старшая медсестра из БД.
     */
    private function resolveSeniorNurseSignerForFooter(RequestRecord $record, string $reportKind): ?User
    {
        $author = $record->author;
        if ($author !== null && $author->is_active && $author->role === 'senior_nurse') {
            return $author;
        }

        $fromRequest = $this->userFromLatestPendingRequestByReportKind($reportKind);
        if ($fromRequest !== null) {
            return $fromRequest;
        }

        return $this->firstActiveUserByRole('senior_nurse');
    }

    private function userFromLatestPendingRequestByReportKind(string $reportKind): ?User
    {
        $typeCodes = match ($reportKind) {
            self::REPORT_KIND_WRITEOFF => [EquipmentRequest::TYPE_WRITEOFF],
            self::REPORT_KIND_MOVE => [EquipmentRequest::TYPE_MOVE],
            default => [EquipmentRequest::TYPE_WRITEOFF, EquipmentRequest::TYPE_MOVE],
        };

        $equipmentRequest = EquipmentRequest::query()
            ->whereRelation('requestStatus', 'code', EquipmentRequest::STATUS_PENDING)
            ->where(function ($q) use ($typeCodes): void {
                foreach ($typeCodes as $idx => $typeCode) {
                    if ($idx === 0) {
                        $q->whereRelation('requestType', 'code', $typeCode);
                    } else {
                        $q->orWhereRelation('requestType', 'code', $typeCode);
                    }
                }
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($equipmentRequest === null) {
            return null;
        }

        return User::query()->whereKey($equipmentRequest->user_id)->first();
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
     * Строки ФИО из данных заявки и карты подстановок — для поиска «голого» текста в HTML и склонения по контексту (DaData).
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $schema
     * @return list<string>
     */
    private function collectPersonNameCandidatesForContextDecline(array $placeholderMap, array $data, array $schema): array
    {
        $out = [];
        foreach (self::PLACEHOLDER_PERSON_NAME_KEYS as $k) {
            $v = trim((string) ($placeholderMap[$k] ?? ''));
            if (mb_strlen($v) >= 4) {
                $out[] = $v;
            }
        }
        $headerOverrides = $data['header_overrides'] ?? null;
        if (is_array($headerOverrides)) {
            foreach ($headerOverrides as $v) {
                if (is_string($v)) {
                    $t = trim($v);
                    if (mb_strlen($t) >= 4) {
                        $out[] = $t;
                    }
                }
            }
        }
        foreach ($schema['fields'] ?? [] as $f) {
            if (! is_array($f)) {
                continue;
            }
            $id = (string) ($f['id'] ?? '');
            if ($id === '' || ! array_key_exists($id, $data)) {
                continue;
            }
            $v = $data[$id];
            if (! is_string($v)) {
                continue;
            }
            $t = trim(strip_tags($v));
            if (mb_strlen($t) >= 4) {
                $out[] = $t;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Сырые строки для подстановки; экранирование в {@see replacePlaceholders}.
     *
     * @return array<string, string>
     */
    private function buildPlaceholderMap(array $data, User $author, User $recipient, RequestRecord $record, string $reportKind): array
    {
        $chiefDoctor = $this->resolveSystemSignerByRole('sign_chief_doctor');
        $departmentHead = $this->resolveDepartmentHeadSignerForReport($reportKind);
        $seniorNurse = $this->resolveSeniorNurseSignerForFooter($record, $reportKind);

        $map = [
            'sys.date' => now()->format('d.m.Y'),
            'sys.chief_doctor_name' => (string) ($chiefDoctor?->name ?? ''),
            'sys.department_head_name' => (string) ($departmentHead?->name ?? ''),
            'sys.senior_nurse_name' => (string) ($seniorNurse?->name ?? ''),
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
     * Экранирование ФИО для PDF после склонения через DaData.
     * Если задан $caseOverride (например genitive после «от»), используется он; иначе падеж из конфига.
     */
    private function pdfPersonName(string $name, ?string $caseOverride = null): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }
        $declined = ($caseOverride !== null && $caseOverride !== '')
            ? $this->dadataNames->declineWithCase($name, $caseOverride)
            : $this->dadataNames->decline($name);

        return e($declined);
    }

    /**
     * Шапка в JSON {@see RequestLayout} → schema.header: приоритет у {@see sections} (до трёх блоков), иначе legacy {@see blocks}.
     *
     * @param  array<string, string>  $map
     * @param  array<string, mixed>  $recordData  сырые data заявки (в т.ч. header_overrides по line_id)
     * @param  array<string, mixed>|null  $headerRoot
     */
    private function renderHeaderBlocks(array $map, ?array $headerRoot, array $recordData = [], string $reportKind = self::REPORT_KIND_DEFAULT): string
    {
        if ($headerRoot === null) {
            return '';
        }

        $headerOverrides = is_array($recordData['header_overrides'] ?? null) ? $recordData['header_overrides'] : [];

        if (isset($headerRoot['sections']) && is_array($headerRoot['sections']) && $headerRoot['sections'] !== []) {
            return $this->renderHeaderSections($map, $headerRoot['sections'], $headerOverrides, $reportKind);
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
    private function renderHeaderSections(array $map, array $sections, array $headerOverrides = [], string $reportKind = self::REPORT_KIND_DEFAULT): string
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
                $beforeAuto = $this->resolveHeaderLineText($line, $headerOverrides);
                $afterAuto = $this->applyAutoHeaderNameOverrides($beforeAuto, $reportKind, $map);
                $raw = $this->declineEditableHeaderPlainTextIfNeeded($beforeAuto, $afterAuto, $line);
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
     */
    private function resolveHeaderLineText(mixed $line, array $headerOverrides): string
    {
        if (is_string($line) || is_numeric($line)) {
            return (string) $line;
        }
        if (! is_array($line)) {
            return '';
        }
        if (! empty($line['role_key']) && is_string($line['role_key'])) {
            return '{{role:'.trim($line['role_key']).'}}';
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

    /**
     * Строка «В заявке» с набранным вручную ФИО (без токена {{role:…}}): если авто-подстановка по фамилиям
     * не сработала, прогоняем текст через DaData — иначе в PDF остаётся именительный падеж из поля ввода.
     */
    private function declineEditableHeaderPlainTextIfNeeded(string $beforeAuto, string $afterAuto, mixed $line): string
    {
        if ($beforeAuto !== $afterAuto) {
            return $afterAuto;
        }
        $text = trim($afterAuto);
        if ($text === '' || str_contains($text, '{{')) {
            return $afterAuto;
        }
        if (! is_array($line) || empty($line['editable']) || ! config('dadata.enabled')) {
            return $afterAuto;
        }

        return $this->dadataNames->decline($text);
    }

    private function applyAutoHeaderNameOverrides(string $line, string $reportKind, array $map): string
    {
        $chiefDoctorName = trim((string) ($map['sys.chief_doctor_name'] ?? ''));
        $departmentHeadName = trim((string) ($map['sys.department_head_name'] ?? ''));
        $seniorNurseName = trim((string) ($map['sys.senior_nurse_name'] ?? ''));
        $normalized = mb_strtolower(trim($line), 'UTF-8');
        if ($chiefDoctorName !== '' && str_contains($normalized, 'гайдаров') && ! str_contains($normalized, 'старшей медсестры')) {
            return $this->dadataNames->decline($chiefDoctorName);
        }
        if ($departmentHeadName !== '' && str_contains($normalized, 'черных')) {
            return $this->dadataNames->decline($departmentHeadName);
        }
        if ($departmentHeadName !== '' && str_contains($normalized, 'гайдаров') && $reportKind === self::REPORT_KIND_WRITEOFF) {
            return $this->dadataNames->decline($departmentHeadName);
        }
        if ($seniorNurseName !== '' && str_contains($normalized, 'ефаров')) {
            return $this->dadataNames->decline($seniorNurseName);
        }

        return $line;
    }

    private function resolveDepartmentHeadSignerForReport(string $reportKind): ?User
    {
        $role = match ($reportKind) {
            self::REPORT_KIND_WRITEOFF => 'sign_writeoff_head',
            self::REPORT_KIND_MOVE => 'sign_move_head',
            default => null,
        };

        if ($role === null) {
            return null;
        }

        return $this->resolveSystemSignerByRole($role);
    }

    private function resolveSystemSignerByRole(string $role): ?User
    {
        $roleId = Role::query()->where('name', $role)->value('id');
        if ($roleId === null) {
            return null;
        }

        return User::query()
            ->where('role_id', (int) $roleId)
            ->orderByDesc('id')
            ->first();
    }

    private function detectReportKind(?string $layoutTitle, array $schema): string
    {
        $haystack = mb_strtolower(
            trim((string) $layoutTitle).' '.trim((string) ($schema['document_title'] ?? '')).' '.trim((string) ($schema['body_html'] ?? '')),
            'UTF-8'
        );

        if (str_contains($haystack, 'перемещ')) {
            return self::REPORT_KIND_MOVE;
        }
        if (str_contains($haystack, 'списани') || str_contains($haystack, 'утилиз')) {
            return self::REPORT_KIND_WRITEOFF;
        }

        return self::REPORT_KIND_DEFAULT;
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
            $full = trim($m[1]);
            $caseOverride = null;
            if (str_contains($full, '|')) {
                $parts = explode('|', $full, 2);
                $full = trim($parts[0]);
                $caseOverride = isset($parts[1]) ? trim($parts[1]) : null;
                if ($caseOverride === '') {
                    $caseOverride = null;
                }
            }
            $key = $full;

            if (str_starts_with($key, 'role:')) {
                $roleKey = trim(substr($key, strlen('role:')));
                if ($roleKey === 'senior_nurse') {
                    $name = trim((string) ($map['sys.senior_nurse_name'] ?? ''));
                    if ($name === '') {
                        $name = $this->resolveRoleSignerName($roleKey);
                    }

                    return $this->pdfPersonName($name, $caseOverride);
                }
                if ($roleKey === 'sign_chief_doctor') {
                    $name = trim((string) ($map['sys.chief_doctor_name'] ?? ''));
                    if ($name === '') {
                        $name = $this->resolveRoleSignerName($roleKey);
                    }

                    return $this->pdfPersonName($name, $caseOverride);
                }
                if ($roleKey === 'sign_writeoff_head' || $roleKey === 'sign_move_head') {
                    $name = trim((string) ($this->resolveSystemSignerByRole($roleKey)?->name ?? ''));
                    if ($name === '') {
                        $name = $this->resolveRoleSignerName($roleKey);
                    }

                    return $this->pdfPersonName($name, $caseOverride);
                }

                return $this->pdfPersonName($this->resolveRoleSignerName($roleKey), $caseOverride);
            }
            $raw = $map[$key] ?? '';
            $str = is_string($raw) ? $raw : '';

            if (str_starts_with($key, 'field:')) {
                if ($caseOverride !== null) {
                    $plain = trim(strip_tags($str));
                    if ($plain === '') {
                        return '';
                    }

                    return e($this->dadataNames->declineWithCase($plain, $caseOverride));
                }

                return $this->sanitizeFieldHtml($str);
            }

            if (in_array($key, self::PLACEHOLDER_PERSON_NAME_KEYS, true)) {
                return $this->pdfPersonName($str, $caseOverride);
            }

            return e($str);
        }, $html) ?? $html;
    }

    private function resolveRoleSignerName(string $roleKey): string
    {
        $roleKey = trim($roleKey);
        if ($roleKey === '') {
            return '';
        }
        if ($roleKey === 'senior_nurse') {
            $u = $this->firstActiveUserByRole('senior_nurse');

            return (string) ($u?->name ?? '');
        }
        if ($roleKey === 'admin' || $roleKey === 'user') {
            $u = $this->firstActiveUserByRole($roleKey);

            return (string) ($u?->name ?? '');
        }

        $u = $this->resolveSystemSignerByRole($roleKey);

        return (string) ($u?->name ?? '');
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

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class DadataNameDeclensionService
{
    private const ENDPOINT = 'https://cleaner.dadata.ru/api/v1/clean/name';

    /** @var array<string, string> */
    private const CASE_TO_FIELD = [
        'nominative' => 'result',
        'genitive' => 'result_genitive',
        'dative' => 'result_dative',
        'ablative' => 'result_ablative',
        'instrumental' => 'result_instrumental',
    ];

    /** @var array<string, string> */
    private const CASE_ALIASES = [
        'gen' => 'genitive',
        'dat' => 'dative',
        'nom' => 'nominative',
        'abl' => 'ablative',
        'ins' => 'instrumental',
    ];

    private readonly string $defaultCaseNormalized;

    public function __construct(
        private readonly bool $enabled,
        private readonly string $token,
        private readonly string $secret,
        string $nameCase,
        private readonly int $timeoutSeconds,
        private readonly int $cacheTtlSeconds,
    ) {
        $this->defaultCaseNormalized = self::normalizeCase($nameCase);
    }

    /**
     * Склоняет ФИО по падежу по умолчанию из конфига (dadata.name_case).
     */
    public function decline(string $source): string
    {
        return $this->declineWithCase($source, $this->defaultCaseNormalized);
    }

    /**
     * Склоняет ФИО в указанный падеж: nominative, genitive, dative, ablative, instrumental
     * (короткие псевдонимы: nom, gen, dat, abl, ins).
     */
    public function declineWithCase(string $source, string $case): string
    {
        $source = trim($source);
        if ($source === '' || ! $this->enabled) {
            return $source;
        }
        if ($this->token === '' || $this->secret === '') {
            return $source;
        }

        $source = $this->normalizeSurnameInitialsTypos($source);

        $caseNorm = self::normalizeCase($case);
        $field = self::CASE_TO_FIELD[$caseNorm] ?? 'result_dative';

        $surnameAndInitials = $this->parseSurnameAndDotInitials($source);
        if ($surnameAndInitials !== null) {
            [$surname, $initialsCompact] = $surnameAndInitials;
            $cacheKey = 'dadata:name:v1:'.$field.':sur:'.sha1($surname);
            $declinedSurname = Cache::remember($cacheKey, max(60, $this->cacheTtlSeconds), function () use ($surname, $field) {
                return $this->fetchDeclined($surname, $field);
            });

            return trim($declinedSurname.' '.$initialsCompact);
        }

        $cacheKey = 'dadata:name:v1:'.$field.':'.sha1($source);

        return Cache::remember($cacheKey, max(60, $this->cacheTtlSeconds), function () use ($source, $field) {
            return $this->fetchDeclined($source, $field);
        });
    }

    /**
     * «Гайдаров Г М» → «Гайдаров Г.М.» — DaData по полной строке часто портит инициалы.
     */
    private function normalizeSurnameInitialsTypos(string $source): string
    {
        if (preg_match('/^([\p{L}\-]+)\s+([А-ЯЁа-яёA-Za-z])\s+([А-ЯЁа-яёA-Za-z])$/u', $source, $m)) {
            return $m[1].' '.$m[2].'.'.$m[3].'.';
        }

        return $source;
    }

    /**
     * Формат «Фамилия И.О.» / «Фамилия И.» — склоняем только фамилию, инициалы не трогаем (так принято в служебных письмах).
     *
     * @return array{0: string, 1: string}|null [фамилия, инициалы подряд «Г.М.»]
     */
    private function parseSurnameAndDotInitials(string $source): ?array
    {
        if (! preg_match('/^([\p{L}\-]+)\s+((?:[А-ЯЁA-Za-zа-яё]\.\s*)+)$/u', $source, $m)) {
            return null;
        }
        $tail = trim($m[2]);
        $compact = preg_replace('/\s+/u', '', $tail);
        if (! preg_match('/^(?:[А-ЯЁA-Za-zа-яё]\.)+$/u', $compact)) {
            return null;
        }
        if (mb_strlen($m[1]) < 2) {
            return null;
        }

        return [$m[1], $compact];
    }

    private function fetchDeclined(string $source, string $resultField): string
    {
        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->withHeaders([
                    'Authorization' => 'Token '.$this->token,
                    'X-Secret' => $this->secret,
                    'Accept' => 'application/json',
                ])
                ->withBody(json_encode([$source], JSON_UNESCAPED_UNICODE), 'application/json')
                ->post(self::ENDPOINT);
        } catch (\Throwable $e) {
            Log::warning('DaData name: HTTP error', ['message' => $e->getMessage()]);

            return $source;
        }

        if (! $response->successful()) {
            Log::warning('DaData name: bad status', ['status' => $response->status(), 'body' => $response->body()]);

            return $source;
        }

        $json = $response->json();
        if (! is_array($json) || ! isset($json[0]) || ! is_array($json[0])) {
            return $source;
        }

        $row = $json[0];
        $out = trim((string) ($row[$resultField] ?? ''));

        if ($out === '') {
            $out = trim((string) ($row['result'] ?? ''));
        }

        return $out !== '' ? $out : $source;
    }

    private static function normalizeCase(string $case): string
    {
        $c = mb_strtolower(trim($case), 'UTF-8');
        if (isset(self::CASE_ALIASES[$c])) {
            $c = self::CASE_ALIASES[$c];
        }

        return array_key_exists($c, self::CASE_TO_FIELD) ? $c : 'dative';
    }
}

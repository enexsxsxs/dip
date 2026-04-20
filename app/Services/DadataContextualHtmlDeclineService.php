<?php

namespace App\Services;

/**
 * Подстановка падежей для ФИО, набранных в HTML обычным текстом (без {{field:…}}).
 * Срабатывает только если строка совпадает с известным ФИО из данных заявки/карты подстановок.
 */
final class DadataContextualHtmlDeclineService
{
    public function __construct(
        private readonly DadataNameDeclensionService $dadata,
    ) {}

    /**
     * @param  list<string>  $candidateNames  уникальные строки ФИО (чем длиннее — тем раньше в списке)
     */
    public function applyToHtml(string $html, array $candidateNames): string
    {
        if ($html === '' || ! config('dadata.enabled') || $candidateNames === []) {
            return $html;
        }

        $names = $this->normalizeCandidates($candidateNames);
        if ($names === []) {
            return $html;
        }

        $parts = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $html;
        }

        $plainBefore = '';
        $out = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match('/^<[^>]+>$/u', $part)) {
                $out .= $part;

                continue;
            }
            $processed = $this->declineInTextSegment($part, $plainBefore, $names);
            $out .= $processed;
            $plainBefore .= $processed;
            if (mb_strlen($plainBefore) > 4000) {
                $plainBefore = mb_substr($plainBefore, -4000);
            }
        }

        return $out;
    }

    /**
     * @param  list<string>  $candidateNames
     * @return list<string>
     */
    private function normalizeCandidates(array $candidateNames): array
    {
        $uniq = [];
        foreach ($candidateNames as $n) {
            $t = trim((string) $n);
            if (mb_strlen($t) < 4) {
                continue;
            }
            $uniq[$t] = true;
        }
        $list = array_keys($uniq);
        usort($list, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return $list;
    }

    /**
     * @param  list<string>  $namesLongestFirst
     */
    private function declineInTextSegment(string $text, string $plainBefore, array $namesLongestFirst): string
    {
        $offset = 0;
        while (true) {
            $bestPos = null;
            $bestName = null;
            $bestCase = null;
            foreach ($namesLongestFirst as $name) {
                $pos = mb_strpos($text, $name, $offset);
                if ($pos === false) {
                    continue;
                }
                $ctx = mb_substr($plainBefore, -2000).mb_substr($text, 0, $pos);
                $case = $this->inferCase(mb_strtolower($ctx, 'UTF-8'));
                if ($case === null) {
                    continue;
                }
                if ($bestPos === null || $pos < $bestPos) {
                    $bestPos = $pos;
                    $bestName = $name;
                    $bestCase = $case;
                }
            }
            if ($bestPos === null || $bestName === null || $bestCase === null) {
                break;
            }
            $declined = $this->dadata->declineWithCase($bestName, $bestCase);
            $len = mb_strlen($bestName);
            $text = mb_substr($text, 0, $bestPos).$declined.mb_substr($text, $bestPos + $len);
            $offset = $bestPos + mb_strlen($declined);
        }

        return $text;
    }

    private function inferCase(string $ctxLower): ?string
    {
        // Контекст — весь текст до имени. «Главному врачу» в начале шапки иначе попадал бы в контекст и для ФИО после «от».
        $posChief = mb_strrpos($ctxLower, 'главному врачу');
        $posSenior = mb_strrpos($ctxLower, 'старшей медсестр');

        if ($posSenior !== false && ($posChief === false || $posSenior > $posChief)) {
            return 'genitive';
        }
        if ($posChief !== false) {
            return 'dative';
        }
        if (preg_match('/заведующ(ему|ей)\s+/u', $ctxLower)) {
            return 'dative';
        }
        if (preg_match('/(отделения|отделению)\s*$/u', $ctxLower)) {
            return 'genitive';
        }
        if (preg_match('/(медсестр|медсестры|отделен|дерматолог|ологическ|клиник|фгбоу|федеральн)/u', $ctxLower)
            && preg_match('/(^|[\s,.:;])от(\s|$)/u', $ctxLower)) {
            return 'genitive';
        }

        return null;
    }
}

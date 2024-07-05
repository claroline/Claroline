<?php

namespace Claroline\CoreBundle\Library\Normalizer;

class CodeNormalizer
{
    private const CODE_LENGTH = 120;
    private const CODE_SEPARATOR = '_';

    public static function normalize(string $code): string
    {
        $normalizedCode = TextNormalizer::stripDiacritics($code);

        // removes multiple whitespaces, new lines & tabs by single whitespace
        $normalizedCode = preg_replace('/\s\s+/', ' ', $normalizedCode);
        $normalizedCode = trim($normalizedCode);
        $normalizedCode = str_replace('.', self::CODE_SEPARATOR, $normalizedCode);
        $normalizedCode = str_replace(' ', self::CODE_SEPARATOR, $normalizedCode);
        // removes all non alpha-numeric chars
        $normalizedCode = preg_replace('/[^a-zA-Z0-9\-_]/', '', $normalizedCode);

        $normalizedCode = strtoupper($normalizedCode);

        return substr($normalizedCode, 0, self::CODE_LENGTH);
    }
}

<?php

namespace Claroline\CoreBundle\Library\Normalizer;

class TextNormalizer
{
    /**
     * @param $string
     *
     * @return string
     */
    public static function stripDiacritics($string)
    {
        $string = (string) $string;

        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $transliteration = [
            // Decompositions for Latin-1 Supplement
            'ª' => 'a', 'º' => 'o', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'Æ' => 'AE', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I',
            'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ö' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 'ß' => 's',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y', 'Ø' => 'O',
            // Decompositions for Latin Extended-A
            'Ā' => 'A', 'ā' => 'a', 'Ă' => 'A', 'ă' => 'a', 'Ą' => 'A', 'ą' => 'a', 'Ć' => 'C', 'ć' => 'c',
            'Ĉ' => 'C', 'ĉ' => 'c', 'Ċ' => 'C', 'ċ' => 'c', 'Č' => 'C', 'č' => 'c', 'Ď' => 'D', 'ď' => 'd',
            'Đ' => 'D', 'đ' => 'd', 'Ē' => 'E', 'ē' => 'e', 'Ĕ' => 'E', 'ĕ' => 'e', 'Ė' => 'E', 'ė' => 'e',
            'Ę' => 'E', 'ę' => 'e', 'Ě' => 'E', 'ě' => 'e', 'Ĝ' => 'G', 'ĝ' => 'g', 'Ğ' => 'G', 'ğ' => 'g',
            'Ġ' => 'G', 'ġ' => 'g', 'Ģ' => 'G', 'ģ' => 'g', 'Ĥ' => 'H', 'ĥ' => 'h', 'Ħ' => 'H', 'ħ' => 'h',
            'Ĩ' => 'I', 'ĩ' => 'i', 'Ī' => 'I', 'ī' => 'i', 'Ĭ' => 'I', 'ĭ' => 'i', 'Į' => 'I', 'į' => 'i',
            'İ' => 'I', 'ı' => 'i', 'Ĳ' => 'IJ', 'ĳ' => 'ij', 'Ĵ' => 'J', 'ĵ' => 'j', 'Ķ' => 'K', 'ķ' => 'k',
            'ĸ' => 'k', 'Ĺ' => 'L', 'ĺ' => 'l', 'Ļ' => 'L', 'ļ' => 'l', 'Ľ' => 'L', 'ľ' => 'l', 'Ŀ' => 'L',
            'ŀ' => 'l', 'Ł' => 'L', 'ł' => 'l', 'Ń' => 'N', 'ń' => 'n', 'Ņ' => 'N', 'ņ' => 'n', 'Ň' => 'N',
            'ň' => 'n', 'ŉ' => 'n', 'Ŋ' => 'N', 'ŋ' => 'n', 'Ō' => 'O', 'ō' => 'o', 'Ŏ' => 'O', 'ŏ' => 'o',
            'Ő' => 'O', 'ő' => 'o', 'Œ' => 'OE', 'œ' => 'oe', 'Ŕ' => 'R', 'ŕ' => 'r', 'Ŗ' => 'R', 'ŗ' => 'r',
            'Ř' => 'R', 'ř' => 'r', 'Ś' => 'S', 'ś' => 's', 'Ŝ' => 'S', 'ŝ' => 's', 'Ş' => 'S', 'ş' => 's',
            'Š' => 'S', 'š' => 's', 'Ţ' => 'T', 'ţ' => 't', 'Ť' => 'T', 'ť' => 't', 'Ŧ' => 'T', 'ŧ' => 't',
            'Ũ' => 'U', 'ũ' => 'u', 'Ū' => 'U', 'ū' => 'u', 'Ŭ' => 'U', 'ŭ' => 'u', 'Ů' => 'U', 'ů' => 'u',
            'Ű' => 'U', 'ű' => 'u', 'Ų' => 'U', 'ų' => 'u', 'Ŵ' => 'W', 'ŵ' => 'w', 'Ŷ' => 'Y', 'ŷ' => 'y',
            'Ÿ' => 'Y', 'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z', 'Ž' => 'Z', 'ž' => 'z', 'ſ' => 's',
            // Decompositions for Latin Extended-B
            'Ș' => 'S', 'ș' => 's', 'Ț' => 'T', 'ț' => 't',
            // Euro Sign
            '€' => 'E',
            // GBP (Pound) Sign
            '£' => '',
            // Vowels with diacritic (Vietnamese)
            // unmarked
            'Ơ' => 'O', 'ơ' => 'o', 'Ư' => 'U', 'ư' => 'u',
            // grave accent
            'Ầ' => 'A', 'ầ' => 'a', 'Ằ' => 'A', 'ằ' => 'a', 'Ề' => 'E', 'ề' => 'e', 'Ồ' => 'O', 'ồ' => 'o',
            'Ờ' => 'O', 'ờ' => 'o', 'Ừ' => 'U', 'ừ' => 'u', 'Ỳ' => 'Y', 'ỳ' => 'y',
            // hook
            'Ả' => 'A', 'ả' => 'a', 'Ẩ' => 'A', 'ẩ' => 'a', 'Ẳ' => 'A', 'ẳ' => 'a', 'Ẻ' => 'E', 'ẻ' => 'e',
            'Ể' => 'E', 'ể' => 'e', 'Ỉ' => 'I', 'ỉ' => 'i', 'Ỏ' => 'O', 'ỏ' => 'o', 'Ổ' => 'O', 'ổ' => 'o',
            'Ở' => 'O', 'ở' => 'o', 'Ủ' => 'U', 'ủ' => 'u', 'Ử' => 'U', 'ử' => 'u', 'Ỷ' => 'Y', 'ỷ' => 'y',
            // tilde
            'Ẫ' => 'A', 'ẫ' => 'a', 'Ẵ' => 'A', 'ẵ' => 'a', 'Ẽ' => 'E', 'ẽ' => 'e', 'Ễ' => 'E', 'ễ' => 'e',
            'Ỗ' => 'O', 'ỗ' => 'o', 'Ỡ' => 'O', 'ỡ' => 'o', 'Ữ' => 'U', 'ữ' => 'u', 'Ỹ' => 'Y', 'ỹ' => 'y',
            // acute accent
            'Ấ' => 'A', 'ấ' => 'a', 'Ắ' => 'A', 'ắ' => 'a', 'Ế' => 'E', 'ế' => 'e', 'Ố' => 'O', 'ố' => 'o',
            'Ớ' => 'O', 'ớ' => 'o', 'Ứ' => 'U', 'ứ' => 'u',
            // dot below
            'Ạ' => 'A', 'ạ' => 'a', 'Ậ' => 'A', 'ậ' => 'a', 'Ặ' => 'A', 'ặ' => 'a', 'Ẹ' => 'E', 'ẹ' => 'e',
            'Ệ' => 'E', 'ệ' => 'e', 'Ị' => 'I', 'ị' => 'i', 'Ọ' => 'O', 'ọ' => 'o', 'Ộ' => 'O', 'ộ' => 'o',
            'Ợ' => 'O', 'ợ' => 'o', 'Ụ' => 'U', 'ụ' => 'u', 'Ự' => 'U', 'ự' => 'u', 'Ỵ' => 'Y', 'ỵ' => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin)
            'ɑ' => 'a',
            // macron
            'Ǖ' => 'U', 'ǖ' => 'u',
            // acute accent
            'Ǘ' => 'U', 'ǘ' => 'u',
            // caron
            'Ǎ' => 'A', 'ǎ' => 'a', 'Ǐ' => 'I', 'ǐ' => 'i', 'Ǒ' => 'O', 'ǒ' => 'o', 'Ǔ' => 'U', 'ǔ' => 'u',
            'Ǚ' => 'U', 'ǚ' => 'u',
            // grave accent
            'Ǜ' => 'U', 'ǜ' => 'u',
        ];
        $string = str_replace(array_keys($transliteration), array_values($transliteration), $string);

        return $string;
    }

    public static function toKey($string, int $length = null)
    {
        $key = static::stripDiacritics($string);
        // removes multiple whitespaces, new lines & tabs by single whitespace
        $key = preg_replace('/\s\s+/', ' ', $key);
        $key = trim($key);
        $key = str_replace(' ', '_', $key);
        $key = str_replace('.', '-', $key);
        // removes all non alpha-numeric chars
        $key = preg_replace('/[^a-zA-Z0-9\-_]/', '', $key);
        // removes uppercase
        $key = strtolower($key);

        if ($length) {
            $key = substr($key, 0, $length);
        }

        return $key;
    }

    public static function toUtf8(string $string): string
    {
        // If encoding not UTF-8 then convert it to UTF-8
        $encoding = mb_detect_encoding($string, ['UTF-8', 'ASCII', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && 'UTF-8' !== $encoding) {
            $string = iconv($encoding, 'UTF-8', $string);
        }

        return $string;
    }

    public static function sanitize(string $string): string
    {
        // If encoding not UTF-8 then convert it to UTF-8
        $string = TextNormalizer::toUtf8($string);

        // normalize end of lines
        $string = str_replace("\r\n", PHP_EOL, $string);
        $string = str_replace("\r", PHP_EOL, $string);

        return $string;
    }

    /**
     * Came from http://j-reaux.developpez.com/tutoriel/php/fonctions-troncature-texte/.
     */
    public static function resumeHtml(string $text, int $nbCharacter, ?string $readMoreText = ''): string
    {
        if (is_numeric($nbCharacter)) {
            $lengthBeforeWithoutHtml = strlen(trim(strip_tags($text)));
            $htmlSplitMask = '#</?([a-zA-Z1-6]+)(?: +[a-zA-Z]+="[^"]*")*( ?/)?>#';
            $htmlMatchMask = '#<(?:/([a-zA-Z1-6]+)|([a-zA-Z1-6]+)(?: +[a-zA-Z]+="[^"]*")*( ?/)?)>#';
            $text .= ' ';
            $textPieces = preg_split($htmlSplitMask, $text, -1, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $pieceNumber = count($textPieces);

            if (1 === $pieceNumber) {
                $text .= ' ';
                $lengthBefore = strlen($text);
                $text = substr($text, 0, strpos($text, ' ', $lengthBefore > $nbCharacter ? $nbCharacter : $lengthBefore));

                if ('' != $readMoreText && $lengthBefore > $nbCharacter) {
                    $text .= $readMoreText;
                }
            } else {
                $length = 0;
                $indexLastPiece = $pieceNumber - 1;
                $position = $textPieces[$indexLastPiece][1] + strlen($textPieces[$indexLastPiece][0]) - 1;
                $indexPiece = $indexLastPiece;
                $searchSpace = true;

                foreach ($textPieces as $index => $bout) {
                    $length += strlen($bout[0]);
                    if ($length >= $nbCharacter) {
                        $positionEndPiece = $bout[1] + strlen($bout[0]) - 1;
                        $position = $positionEndPiece - ($length - $nbCharacter);

                        $positionSpace = strpos($bout[0], ' ', $position - $bout[1]);
                        if (false !== $positionSpace) {
                            $position = $bout[1] + $positionSpace;
                            $searchSpace = false;
                        }
                        if ($index != $indexLastPiece) {
                            $indexPiece = $index + 1;
                        }
                        break;
                    }
                }

                if (true === $searchSpace) {
                    for ($i = $indexPiece; $i <= $indexLastPiece; ++$i) {
                        $position = $textPieces[$i][1];
                        $positionSpace = strpos($textPieces[$i][0], ' ');
                        if (false !== $positionSpace) {
                            $position += $positionSpace;
                            break;
                        }
                    }
                }

                $text = substr($text, 0, $position);
                preg_match_all($htmlMatchMask, $text, $return, PREG_OFFSET_CAPTURE);
                $tagPieces = [];

                foreach ($return[0] as $index => $tag) {
                    if (isset($return[3][$index][0])) {
                        continue;
                    }
                    if ('/' != $return[0][$index][0][1]) {
                        array_unshift($tagPieces, $return[2][$index][0]);
                    } else {
                        array_shift($tagPieces);
                    }
                }

                if (!empty($tagPieces)) {
                    foreach ($tagPieces as $tag) {
                        $text .= '</'.$tag.'>';
                    }
                }

                if ('' != $readMoreText && $lengthBeforeWithoutHtml > $nbCharacter) {
                    $text .= 'SuspensionPoint';
                    $pattern = '#((</[^>]*>[\n\t\r ]*)?(</[^>]*>[\n\t\r ]*)?(</[^>]*>[\n\t\r ]*)?(</[^>]*>[\n\t\r ]*)?(</[^>]*>)[\n\t\r ]*SuspensionPoint)#i';
                    $text = preg_replace($pattern, $readMoreText.'${2}${3}${4}${5}${6}', $text);
                }
            }
        }

        return $text;
    }
}

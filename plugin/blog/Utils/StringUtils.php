<?php

namespace Icap\BlogBundle\Utils;

class StringUtils
{
    /**
     * Came from http://j-reaux.developpez.com/tutoriel/php/fonctions-troncature-texte/.
     *
     * @param string $text
     * @param int    $nbCaracter
     * @param string $readMoreText
     *
     * @return string
     */
    public static function resumeHtml($text, $nbCaracter, $readMoreText = '')
    {
        if (is_numeric($nbCaracter)) {
            $lengthBeforeWithoutHtml = strlen(trim(strip_tags($text)));
            $htmlSplitMask = '#</?([a-zA-Z1-6]+)(?: +[a-zA-Z]+="[^"]*")*( ?/)?>#';
            $htmlMatchMask = '#<(?:/([a-zA-Z1-6]+)|([a-zA-Z1-6]+)(?: +[a-zA-Z]+="[^"]*")*( ?/)?)>#';
            $text                   .= ' ';
            $textPieces = preg_split($htmlSplitMask, $text, -1, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $pieceNumber = count($textPieces);

            if ($pieceNumber == 1) {
                $text        .= ' ';
                $lenghtBefore = strlen($text);
                $text = substr($text, 0, strpos($text, ' ', $lenghtBefore > $nbCaracter ? $nbCaracter : $lenghtBefore));

                if ($readMoreText != '' && $lenghtBefore > $nbCaracter) {
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
                    if ($length >= $nbCaracter) {
                        $positionEndPiece = $bout[1] + strlen($bout[0]) - 1;
                        $position = $positionEndPiece - ($length - $nbCaracter);
                        if (($positionSpace = strpos($bout[0], ' ', $position - $bout[1])) !== false) {
                            $position = $bout[1] + $positionSpace;
                            $searchSpace = false;
                        }
                        if ($index != $indexLastPiece) {
                            $indexPiece = $index + 1;
                        }
                        break;
                    }
                }

                if ($searchSpace === true) {
                    for ($i = $indexPiece; $i <= $indexLastPiece; ++$i) {
                        $position = $textPieces[$i][1];
                        if (($positionSpace = strpos($textPieces[$i][0], ' ')) !== false) {
                            $position += $positionSpace;
                            break;
                        }
                    }
                }

                $text = substr($text, 0, $position);
                preg_match_all($htmlMatchMask, $text, $return, PREG_OFFSET_CAPTURE);
                $tagPieces = array();

                foreach ($return[0] as $index => $tag) {
                    if (isset($return[3][$index][0])) {
                        continue;
                    }
                    if ($return[0][$index][0][1] != '/') {
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

                if ($readMoreText != '' && $lengthBeforeWithoutHtml > $nbCaracter) {
                    $text   .= 'SuspensionPoint';
                    $pattern = '#((</[^>]*>[\n\t\r ]*)?(</[^>]*>[\n\t\r ]*)?(</[^>]*>[\n\t\r ]*)?(</[^>]*>[\n\t\r ]*)?(</[^>]*>)[\n\t\r ]*SuspensionPoint)#i';
                    $text = preg_replace($pattern, $readMoreText.'${2}${3}${4}${5}${6}', $text);
                }
            }
        }

        return $text;
    }
}

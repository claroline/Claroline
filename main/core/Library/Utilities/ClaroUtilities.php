<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ClaroUtilities
{
    private $container;
    private $hasIntl;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->hasIntl = extension_loaded('intl');
    }

    /**
     * Detect if encoding is UTF-8, ASCII, ISO-8859-1 or Windows-1252.
     *
     * @param $string
     *
     * @return bool|string
     */
    public function detectEncoding($string)
    {
        static $enclist = ['UTF-8', 'ASCII', 'ISO-8859-1', 'Windows-1252'];

        if (function_exists('mb_detect_encoding')) {
            return mb_detect_encoding($string, $enclist, true);
        }

        $result = false;

        foreach ($enclist as $item) {
            try {
                $sample = iconv($item, $item, $string);
                if (md5($sample) === md5($string)) {
                    $result = $item;
                    break;
                }
            } catch (\Exception $e) {
                unset($e);
            }
        }

        return $result;
    }

    public function html2Csv($htmlStr, $preserveMedia = false)
    {
        $csvStr = $this->formatCsvOutput($htmlStr);
        if ($preserveMedia) {
            $csvStr = strip_tags($csvStr, '<img><embed><video><audio><source>');
            // On Image and Embed objects, keep src
            $csvStr = preg_replace(
                '/<(img|embed)([^>]+src=[\'"]([^\'"]+)[\'"])*[^\/>]*\/?>/i',
                '[$1 src="$3"]',
                $csvStr
            );
            // On Video and Audio keep sources
            $csvStr = preg_replace_callback(
                '/<(video|audio)([^>]+src=[\'"]([^\'"]+)[\'"])*[^\/>]*\/?>([\s\S]*)<\/\1>/i',
                function ($matches) {
                    return $this->mediaSrcExtractor($matches);
                },
                $csvStr
            );
        }
        // Strip any remaining tags
        $csvStr = strip_tags($csvStr);
        // Trim spaces
        $csvStr = trim(preg_replace('/\s+/', ' ', $csvStr));

        return $csvStr;
    }

    private function mediaSrcExtractor($matches)
    {
        $ret = '['.$matches[1].(empty($matches[3]) ? '' : ' src="'.$matches[3].'"');
        if (!empty($matches[4])) {
            $srcs = [];
            preg_match_all('/src=[\'"]([^\'"]+)[\'"]/', $matches[4], $srcs);
            foreach ($srcs[1] as $src) {
                $ret .= ' src="'.$src.'"';
            }
        }
        $ret .= ']';

        return $ret;
    }

    private function formatCsvOutput($data)
    {
        // If encoding not UTF-8 then convert it to UTF-8
        $data = $this->stringToUtf8($data);
        $data = str_replace("\r\n", PHP_EOL, $data);
        $data = str_replace("\r", PHP_EOL, $data);
        $data = str_replace("\n", PHP_EOL, $data);

        return $data;
    }

    private function stringToUtf8($string)
    {
        // If encoding not UTF-8 then convert it to UTF-8
        $encoding = $this->detectEncoding($string);
        if ($encoding && 'UTF-8' !== $encoding) {
            $string = iconv($encoding, 'UTF-8', $string);
        }

        return $string;
    }
}

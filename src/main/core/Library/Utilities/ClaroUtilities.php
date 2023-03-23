<?php

namespace Claroline\CoreBundle\Library\Utilities;

use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;

class ClaroUtilities
{
    public function html2Csv(string $htmlStr, ?bool $preserveMedia = false): string
    {
        $csvStr = TextNormalizer::sanitize($htmlStr);
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
        return trim(preg_replace('/\s+/', ' ', $csvStr));
    }

    private function mediaSrcExtractor(array $matches): string
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
}

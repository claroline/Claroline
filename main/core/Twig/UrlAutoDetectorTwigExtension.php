<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 2/22/16
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class UrlAutoDetectorTwigExtension.
 *
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class UrlAutoDetectorTwigExtension extends \Twig_Extension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'url_auto_detector_extension';
    }

    /**
     * @return array of twig filters
     */
    public function getFilters()
    {
        return array(
            'url_detect' => new \Twig_Filter_Method(
                $this,
                'autoDetectUrls',
                array(
                    'pre_escape' => 'html',
                    'is_safe' => array('html'),
                )
            ),
        );
    }

    /**
     * Find all urls in string and encapsulate them with <a> tag.
     *
     * @param $string
     *
     * @return mixed|$string
     */
    public function autoDetectUrls($string)
    {
        $pattern = '/(<a\b[^>]*>\s*|href="|src=")?([-a-zA-Zа-яёА-ЯЁ0-9@:%_\+.~#?&\/\/=]{2,256}\.[a-zа-яё]{2,4}\b(\/?([-\p{L}0-9@:%_\+~#&\/\/=\(\)]|[.?,](?!\s|$))*)?)/u';
        $stringFiltered = preg_replace_callback($pattern, array($this, 'callbackReplace'), $string);

        return $stringFiltered;
    }

    /**
     * For every url match in string encapsulate if needed and return string.
     *
     * @param array $matches
     *
     * @return string
     */
    public function callbackReplace($matches)
    {
        if ($matches[1] !== '') {
            return $matches[0]; // don't modify existing <a href="">links</a> and <img src="">
        }
        $url = $matches[2];
        $urlWithPrefix = $matches[2];
        if (strpos($url, '@') !== false) {
            $urlWithPrefix = 'mailto:'.$url;
        } elseif (strpos($url, 'https://') === 0) {
            $urlWithPrefix = $url;
        } elseif (strpos($url, 'http://') !== 0) {
            $urlWithPrefix = 'http://'.$url;
        }

        return '<a href="'.$urlWithPrefix.'">'.$url.'</a>';
    }
}

<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/24/15
 */

namespace Icap\SocialmediaBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class SocialmediaExtension.
 *
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class SocialmediaExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('smartCounter', array($this, 'getSmartCounter')),
        );
    }

    public function getSmartCounter($number, $precision = 1)
    {
        static $suffixes = array('', 'k', 'M', 'B', 'T');
        $number = intval($number);
        if ($number == 0) {
            return '&nbsp;';
        }
        $base = log($number) / log(1000);

        return round(pow(1000, $base - floor($base)), $precision).$suffixes[floor($base)];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'social_media_extension';
    }
}

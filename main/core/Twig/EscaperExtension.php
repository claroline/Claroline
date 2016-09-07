<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @Service
 * @Tag("twig.extension")
 */
class EscaperExtension extends \Twig_Extension
{
    protected $content;

    public function getFilters()
    {
        return [
            'ng_escape' => new \Twig_Filter_Method($this, 'ngEscape'),
        ];
    }

    public function getName()
    {
        return 'escaper_extension';
    }

    public function ngEscape($content)
    {
        $content = str_replace('"', '\"', $content);
        $content = str_replace('\'', '\\\'', $content);

        return $content;
    }
}

<?php

namespace HeVinci\CompetencyBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class Extension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('em', [$this, 'emphasizeFilter']),
        ];
    }

    public function emphasizeFilter($string)
    {
        return "<em>{$string}</em>";
    }

    public function getName()
    {
        return 'hevinci_competency_extension';
    }
}

<?php

namespace Claroline\AgendaBundle\Twig;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @Service
 * @Tag("twig.extension")
 */
class AgendaExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('convertDateTimeToString', array($this, 'convertDateTimeToString')),
        );
    }

    public function getName()
    {
        return 'agenda_extension';
    }

    public function convertDateTimeToString()
    {
        return '';
    }
}
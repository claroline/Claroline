<?php

namespace UJM\LtiBundle\Manager\twig;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class Params extends \Twig_Extension
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Get name.
     */
    public function getName()
    {
        return 'twigLti';
    }

    /**
     * Get functions.
     */
    public function getFunctions()
    {
        return [
            'printfTwig' => new \Twig_Function_Method($this, 'printfTwig'),
        ];
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function printfTwig($url)
    {
        return sprintf($url);
    }
}

<?php

namespace Claroline\CoreBundle\Library\Twig;

use Twig_Extension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Claroline\CoreBundle\Library\Resource\Mode;

/**
 * Adds the isPathMode var to the twig Globals. It's used by the
 * activity player to remove the resource context.s
 */
class CamlCaseToUnderscoreFilterExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            'caml_case_to_underscore' => new \Twig_Filter_Method($this, 'camlCaseToUnderscoreFilter'),
        );
    }

    public function camlCaseToUnderscoreFilter($string)
    {
        $newString = preg_replace('`([^a-z])`', '_$1', $string);

        return strtolower($newString); 
    }

    public function getName()
    {
        return 'caml_case_to_underscore_extension';
    }
}
<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 11/18/15
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class VarTypeExtension extends \Twig_Extension
{
    protected $env;

    public function getName()
    {
        return 'twig_var_type_extension';
    }

    public function getFunctions()
    {
        return array(
            'is_int' => new \Twig_Function_Method($this, 'isInt'),
        );
    }

    public function isInt($var)
    {
        return preg_match('/\d+/', $var);
    }
}

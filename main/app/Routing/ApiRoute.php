<?php

namespace Claroline\AppBundle\Routing;

use Symfony\Component\Routing\Route;

class ApiRoute extends Route
{
    /** @var string */
    private $class;

    public function __construct($pattern, $defaults, $requirements = [])
    {
        parent::__construct($pattern, $defaults, $requirements);
        $this->class = $defaults['class'];
    }

    public function getClass()
    {
        return $this->class;
    }
}

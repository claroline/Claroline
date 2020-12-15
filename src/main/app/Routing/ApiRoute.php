<?php

namespace Claroline\AppBundle\Routing;

use Symfony\Component\Routing\Route;

class ApiRoute extends Route
{
    /** @var string */
    private $class;

    /** @var string */
    private $action;

    public function __construct($pattern, $defaults, $requirements = [])
    {
        parent::__construct($pattern, $defaults, $requirements);
        $this->class = $defaults['class'];
        $this->action = null;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }
}

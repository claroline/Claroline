<?php

namespace Claroline\CoreBundle\Library\View;

use FOS\RestBundle\View\ViewHandler as BaseHandler;

class ViewHandler extends BaseHandler
{
    public function getContainer()
    {
        return $this->container;
    }
}

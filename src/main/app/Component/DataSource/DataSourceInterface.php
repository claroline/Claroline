<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AppBundle\Component\Context\ContextualInterface;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;

interface DataSourceInterface extends ComponentInterface, ContextualInterface
{
    public function getData(GetDataEvent $event): void;
}

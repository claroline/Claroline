<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Claroline\AppBundle\Event\DataConveyorEventInterface;
use Claroline\AppBundle\Event\MandatoryEventInterface;
use Symfony\Component\EventDispatcher\Event;

class BuildBreadcrumbEvent extends Event implements MandatoryEventInterface, DataConveyorEventInterface
{
    private $object;
    private $breadcrumb;
    private $isPopulated;

    public function __construct($object)
    {
        $this->object = $object;
        $this->isPopulated = false;
        $this->breadcrumb = [];
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getBreadcrumb()
    {
        return $this->breadcrumb;
    }

    public function setBreadcrumb(array $breadcrumb)
    {
        $this->breadcrumb = $breadcrumb;
        $this->isPopulated = true;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}

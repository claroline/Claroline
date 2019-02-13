<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource soft deletion is asked.
 */
class SoftDeleteResourceEvent extends Event
{
    /** @var AbstractResource */
    private $resource;

    /**
     * SoftDeleteResourceEvent constructor.
     *
     * @param AbstractResource $resource
     */
    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }
}

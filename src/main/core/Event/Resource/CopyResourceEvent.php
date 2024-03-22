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
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource copy is asked.
 */
class CopyResourceEvent extends Event
{
    public function __construct(
        private readonly AbstractResource $resource,
        private readonly AbstractResource $copy
    ) {
    }

    /**
     * Returns the instance of the resource to be copied.
     */
    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    /**
     * Returns the copy instance of the resource.
     */
    public function getCopy(): AbstractResource
    {
        return $this->copy;
    }
}

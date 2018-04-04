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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Component\EventDispatcher\Event;

class LoadFileEvent extends Event
{
    private $resource;

    /** @var array */
    private $data = [];

    /**
     * LoadFileEvent constructor.
     *
     * @param File $resource
     */
    public function __construct(File $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the resource on which the action is to be taken.
     *
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     *
     * @param array $data
     */
    public function setAdditionalData(array $data)
    {
        $this->data = $data;
    }

    public function getAdditionalData()
    {
        return $this->data;
    }
}

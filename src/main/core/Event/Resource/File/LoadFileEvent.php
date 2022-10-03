<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Resource\File;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Contracts\EventDispatcher\Event;

class LoadFileEvent extends Event
{
    /** @var File */
    private $resource;

    /** @var array */
    private $data = [];

    /** @var string */
    private $path;

    /** @var bool */
    private $populated = false;

    public function __construct(File $resource, string $path)
    {
        $this->resource = $resource;
        $this->path = $path;
    }

    public function isPopulated()
    {
        return $this->populated;
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
     * Gets the path to the real file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->populated = true;
    }

    public function getData()
    {
        return $this->data;
    }
}

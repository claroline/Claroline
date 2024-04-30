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
    private array $data = [];

    private bool $populated = false;

    public function __construct(
        private readonly File $resource,
        private readonly string $path
    ) {
    }

    public function isPopulated(): bool
    {
        return $this->populated;
    }

    /**
     * Returns the resource on which the action is to be taken.
     */
    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    /**
     * Gets the path to the real file.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function setData(array $data): void
    {
        $this->data = $data;
        $this->populated = true;
    }

    public function getData(): array
    {
        return $this->data;
    }
}

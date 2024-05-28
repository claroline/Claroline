<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Event\Crud;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Crud event class.
 */
class CrudEvent extends Event
{
    private bool $block = false;

    public function __construct(
        private readonly mixed $object,
        private readonly array $options = []
    ) {
    }

    public function getClass(): string
    {
        // FIXME : this can return a Doctrine Proxy
        return get_class($this->object);
    }

    public function getObject(): mixed
    {
        return $this->object;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @deprecated only used by Organization to avoid deleting the default Organization.
     */
    public function block(): void
    {
        $this->block = true;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return !$this->block;
    }
}

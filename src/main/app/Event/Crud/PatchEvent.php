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

class PatchEvent extends CrudEvent
{
    public function __construct(
        mixed $object,
        array $options,
        private readonly string $property,
        private readonly mixed $value,
        private readonly string $action
    ) {
        parent::__construct($object, $options);
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}

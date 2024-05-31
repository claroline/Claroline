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

class CopyEvent extends CrudEvent
{
    public function __construct(
        mixed $object,
        array $options,
        private readonly mixed $copy,
        private readonly array $extra
    ) {
        parent::__construct($object, $options);
    }

    public function getCopy(): mixed
    {
        return $this->copy;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }
}

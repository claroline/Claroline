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

class CreateEvent extends CrudEvent
{
    public function __construct(
        mixed $object,
        array $options = [],
        private readonly array $data = []
    ) {
        parent::__construct($object, $options);
    }

    public function getData(): array
    {
        return $this->data;
    }
}

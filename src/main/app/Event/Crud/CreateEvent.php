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

/**
 * Crud event class.
 */
class CreateEvent extends CrudEvent
{
    private $data;

    /**
     * @param mixed $object  - The object created
     * @param array $options - An array of options
     */
    public function __construct($object, array $options = [], array $data = [])
    {
        parent::__construct($object, $options);

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}

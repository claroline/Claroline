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
class UpdateEvent extends CrudEvent
{
    public function __construct($object, array $options, array $oldData = [])
    {
        parent::__construct($object, $options);

        $this->oldData = $oldData;
    }

    public function getOldData()
    {
        return $this->oldData;
    }
}

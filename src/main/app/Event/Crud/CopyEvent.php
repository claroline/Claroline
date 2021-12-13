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
    /** @var mixed */
    private $copy;
    /** @var mixed */
    private $extra;

    /**
     * @param mixed $object  - The object created
     * @param array $options - An array of options
     * @param mixed $copy    - The copied entity
     */
    public function __construct($object, array $options, $copy, $extra)
    {
        parent::__construct($object, $options);

        $this->copy = $copy;
        $this->extra = $extra;
    }

    /**
     * @return mixed $object
     */
    public function getCopy()
    {
        return $this->copy;
    }

    /**
     * @return mixed $object
     */
    public function getExtra()
    {
        return $this->extra;
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Security;

use Doctrine\Common\Collections\ArrayCollection;

class ObjectCollection extends ArrayCollection
{
    private $class;
    private $options;

    public function __construct(array $objects, array $options = [])
    {
        $this->class = get_class($objects[0]);

        foreach ($objects as $object) {
            if (get_class($object) !== $this->class) {
                throw new \Exception('Classes of objects are varying.');
            }
        }

        parent::__construct($objects);
        $this->options = $options;
    }

    public function addOption($option)
    {
        $this->options[] = $option;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getClass()
    {
        return $this->class;
    }
}

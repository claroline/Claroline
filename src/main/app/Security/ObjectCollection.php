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
    /** @var array */
    private $options;

    public function __construct(array $objects, array $options = [])
    {
        /*foreach ($objects as $object) {
            if (!$object instanceof $objects[0]) {
                $classes = '';
                foreach ($objects as $obj) {
                    $classes .= get_class($obj).', ';
                }
                throw new \Exception('Classes of objects are varying. '.$classes);
            }
        }*/

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

    public function isInstanceOf($class)
    {
        //doctrine sends proxy so we have to do the check with the instanceof operator
        $rc = new \ReflectionClass($class);
        $toCheck = $rc->newInstanceWithoutConstructor();

        return $this->first() instanceof $toCheck;
    }
}

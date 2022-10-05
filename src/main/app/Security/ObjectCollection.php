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

    public function __construct(array $objects, ?array $options = [])
    {
        parent::__construct($objects);

        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $optionKey)
    {
        if ($this->options[$optionKey]) {
            return $this->options[$optionKey];
        }

        return null;
    }

    public function isInstanceOf($class): bool
    {
        //doctrine sends proxy so we have to do the check with the instanceof operator
        $rc = new \ReflectionClass($class);
        $toCheck = $rc->newInstanceWithoutConstructor();

        return $this->first() instanceof $toCheck;
    }
}

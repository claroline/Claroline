<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Rule\Constraints;

use Claroline\CoreBundle\Rule\Entity\Rule;

abstract class AbstractConstraint implements ConstraintInterface
{
    /**
     * @var \Claroline\CoreBundle\Rule\Entity\Rule
     */
    private $rule;

    /**
     * @var \Claroline\CoreBundle\Entity\Log\Log[]
     */
    private $associatedLogs;

    /**
     * @throws \RuntimeException
     *
     * @return \Claroline\CoreBundle\Rule\Entity\Rule
     */
    public function getRule()
    {
        if (null === $this->rule) {
            throw new \RuntimeException('No rule given to the constraint. How can it validate something without rule to validate?');
        }

        return $this->rule;
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Entity\Rule $rule
     *
     * @return AbstractConstraint
     */
    public function setRule($rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * @throws \RuntimeException
     *
     * @return \Claroline\CoreBundle\Entity\Log\Log[]
     */
    public function getAssociatedLogs()
    {
        if (null === $this->associatedLogs) {
            throw new \RuntimeException('No associated logs given to the constraint. How can it validate something without something to validate?');
        }

        return $this->associatedLogs;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Log\Log[] $associatedLogs
     *
     * @return AbstractConstraint
     */
    public function setAssociatedLogs($associatedLogs)
    {
        $this->associatedLogs = $associatedLogs;

        return $this;
    }
}

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
use Claroline\CoreBundle\Entity\Log\Log;

abstract class AbstractConstraint implements ConstraintInterface
{
    /**
     * @var \Claroline\CoreBundle\Rule\Entity\Rule
     */
    protected $rule;

    /**
     * @var \Claroline\CoreBundle\Entity\Log\Log[]
     */
    protected $associatedLogs;

    /**
     * @param Rule $rule
     *
     * @param Log[] $associatedLogs
     *
     * @return \Claroline\CoreBundle\Rule\Constraints\AbstractConstraint
     */
    public function __construct(Rule $rule, $associatedLogs)
    {
        $this->rule           = $rule;
        $this->associatedLogs = $associatedLogs;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Rule\Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Log\Log[]
     */
    public function getAssociatedLogs()
    {
        return $this->associatedLogs;
    }
}

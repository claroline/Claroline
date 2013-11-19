<?php

namespace Claroline\CoreBundle\Badge\Constraints;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

abstract class AbstractConstraint implements ConstraintInterface
{
    /**
     * @var \Claroline\CoreBundle\Entity\Badge\BadgeRule
     */
    protected $badgeRule;

    /**
     * @var \Claroline\CoreBundle\Entity\Log\Log[]
     */
    protected $associatedLogs;

    /**
     * @param BadgeRule $badgeRule
     *
     * @param Log[]     $associatedLogs
     *
     * @return \Claroline\CoreBundle\Badge\Constraints\AbstractConstraint
     */
    public function __construct(BadgeRule $badgeRule, $associatedLogs)
    {
        $this->badgeRule      = $badgeRule;
        $this->associatedLogs = $associatedLogs;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\BadgeRule
     */
    public function getBadgeRule()
    {
        return $this->badgeRule;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Log\Log[]
     */
    public function getAssociatedLogs()
    {
        return $this->associatedLogs;
    }
}

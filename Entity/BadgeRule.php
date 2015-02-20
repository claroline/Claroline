<?php

namespace Icap\BadgeBundle\Entity\Badge;

use Claroline\CoreBundle\Rule\Entity\Rule;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BadgeRule
 *
 * @ORM\Table(name="claro_badge_rule")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Badge\BadgeRuleRepository")
 */
class BadgeRule extends Rule
{
    /**
     * @var Badge
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Badge\Badge", inversedBy="badgeRules")
     * @ORM\JoinColumn(name="associated_badge", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $associatedBadge;

    /**
     * @param \Claroline\CoreBundle\Entity\Badge\Badge $badge
     *
     * @return BadgeRule
     */
    public function setAssociatedBadge($badge)
    {
        $this->associatedBadge = $badge;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\Badge|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getAssociatedBadge()
    {
        return $this->associatedBadge;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id              = null;
            $this->associatedBadge = null;
        }
    }
}

<?php

namespace Icap\BadgeBundle\Entity;

use Claroline\CoreBundle\Rule\Entity\Rule;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BadgeRule
 *
 * @ORM\Table(name="claro_badge_rule")
 * @ORM\Entity(repositoryClass="Icap\BadgeBundle\Repository\BadgeRuleRepository")
 */
class BadgeRule extends Rule
{
    /**
     * @var Badge
     *
     * @ORM\ManyToOne(targetEntity="Icap\BadgeBundle\Entity\Badge", inversedBy="badgeRules")
     * @ORM\JoinColumn(name="associated_badge", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $associatedBadge;

    /**
     * @param \Icap\BadgeBundle\Entity\Badge $badge
     *
     * @return BadgeRule
     */
    public function setAssociatedBadge($badge)
    {
        $this->associatedBadge = $badge;

        return $this;
    }

    /**
     * @return \Icap\BadgeBundle\Entity\Badge|\Doctrine\Common\Collections\ArrayCollection
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

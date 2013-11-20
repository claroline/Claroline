<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Badge;

use Claroline\CoreBundle\Badge\Constraints\ResultConstraint;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Rule\Rule;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class BadgeRule
 *
 * @ORM\Table(name="claro_badge_rule")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Badge\BadgeRuleRepository")
 */
class BadgeRule extends Rule
{
    /**
     * @var Badge[]
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Badge\Badge", inversedBy="badgeRules")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $badge;

    /**
     * @param \Claroline\CoreBundle\Entity\Badge\Badge $badge
     *
     * @return BadgeRule
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\Badge[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBadge()
    {
        return $this->badge;
    }
}

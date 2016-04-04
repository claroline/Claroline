<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Activity;

use Claroline\CoreBundle\Rule\Entity\Rule;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Activity\ActivityRuleRepository")
 * @ORM\Table(name="claro_activity_rule")
 */
class ActivityRule extends Rule
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityParameters",
     *     inversedBy="rules"
     * )
     * @ORM\JoinColumn(name="activity_parameters_id", onDelete="CASCADE", nullable=false)
     */
    protected $activityParameters;

    /**
     * @ORM\Column(name="result_visible", type="boolean", nullable=true)
     */
    protected $isResultVisible;

    public function __construct()
    {
        $this->occurrence = 1;
    }

    public function getActivityParameters()
    {
        return $this->activityParameters;
    }

    public function setActivityParameters($activityParameters)
    {
        $this->activityParameters = $activityParameters;
    }

    public function getIsResultVisible()
    {
        return $this->isResultVisible;
    }

    public function setIsResultVisible($isResultVisible)
    {
        $this->isResultVisible = $isResultVisible;
    }
}

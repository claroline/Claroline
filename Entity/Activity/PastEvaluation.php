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

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Activity\PastEvaluationRepository")
 * @ORM\Table(name="claro_activity_past_evaluation")
 */
class PastEvaluation extends AbstractEvaluation
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityParameters")
     * @ORM\JoinColumn(name="activity_parameters_id", onDelete="SET NULL")
     */
    protected $activityParameters;

    /**
     * @ORM\Column(name="evaluation_date", type="datetime", nullable=true)
     */
    protected $date;

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getActivityParameters()
    {
        return $this->activityParameters;
    }

    public function setActivityParameters($activityParameters)
    {
        $this->activityParameters = $activityParameters;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }
}

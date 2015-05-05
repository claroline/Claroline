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
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Activity\EvaluationRepository")
 * @ORM\Table(
 *     name="claro_activity_evaluation",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="user_activity_unique_evaluation",
 *             columns={"user_id", "activity_parameters_id"}
 *         )
 *     }
 * )
 */
class Evaluation extends AbstractEvaluation
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityParameters")
     * @ORM\JoinColumn(name="activity_parameters_id", onDelete="CASCADE", nullable=false)
     */
    protected $activityParameters;

    /**
     * @ORM\Column(name="lastest_evaluation_date", type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\Column(name="total_duration", type="integer", nullable=true)
     */
    protected $attemptsDuration;

    /**
     * @ORM\Column(name="attempts_count", type="integer", nullable=true)
     */
    protected $attemptsCount;

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

    public function getAttemptsDuration()
    {
        return $this->attemptsDuration;
    }

    public function setAttemptsDuration($attemptsDuration)
    {
        $this->attemptsDuration = $attemptsDuration;
    }

    public function getAttemptsCount()
    {
        return $this->attemptsCount;
    }

    public function setAttemptsCount($attemptsCount)
    {
        $this->attemptsCount = $attemptsCount;
    }
}

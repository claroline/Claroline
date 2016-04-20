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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Rule\Rulable;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Activity parameters
 * Defines the context of the Activity.
 *
 * @ORM\Entity
 * @ORM\Table(name="claro_activity_parameters")
 */
class ActivityParameters extends Rulable
{
    /**
     * Unique identifier of the parameters.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Activity.
     *
     * @var \Claroline\CoreBundle\Entity\Resource\Activity
     *
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\Activity",
     *     mappedBy="parameters"
     * )
     * @ORM\JoinColumn(name="activity_id", onDelete="CASCADE", nullable=true)
     */
    protected $activity;

    /**
     * Max duration allowed.
     *
     * @var int
     *
     * @ORM\Column(name="max_duration", type="integer", nullable=true)
     */
    protected $maxDuration;

    /**
     * Max attempts allowed.
     *
     * @var int
     *
     * @ORM\Column(name="max_attempts", type="integer", nullable=true)
     */
    protected $maxAttempts;

    /**
     * By who the activity must be done.
     *
     * @var string
     *
     * @ORM\Column(name="who", type="string", nullable=true)
     */
    protected $who;

    /**
     * Where the Activity must be done.
     *
     * @var string
     *
     * @ORM\Column(name="activity_where", type="string", nullable=true)
     */
    protected $where;

    /**
     * Is this activity need a tutor ?
     *
     * @var bool
     *
     * @ORM\Column(name="with_tutor", type="boolean", nullable=true)
     */
    protected $withTutor;

    /**
     * Secondary resources.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode"
     * )
     * @ORM\JoinTable(name="claro_activity_secondary_resources")
     */
    protected $secondaryResources;

    /**
     * Type of evaluation.
     *
     * @var string
     *
     * @ORM\Column(name="evaluation_type", nullable=true)
     */
    protected $evaluationType;

    /**
     * Rules for the Activity.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityRule",
     *     mappedBy="activityParameters"
     * )
     */
    protected $rules;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->secondaryResources = new ArrayCollection();
        $this->rules = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get activity.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set activity.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\Activity $activity
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setActivity(Activity $activity = null)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get max duration.
     *
     * @return int
     */
    public function getMaxDuration()
    {
        return $this->maxDuration;
    }

    /**
     * Set max duration.
     *
     * @param int $maxDuration
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setMaxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;

        return $this;
    }

    /**
     * Get max attempts.
     *
     * @return int
     */
    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }

    /**
     * Set max attempts.
     *
     * @param int $maxAttempts
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    /**
     * Get who.
     *
     * @return string
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * Set who.
     *
     * @param string $who
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setWho($who)
    {
        $this->who = $who;

        return $this;
    }

    /**
     * Get where.
     *
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Set where.
     *
     * @param string $where
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setWhere($where)
    {
        $this->where = $where;

        return $this;
    }

    /**
     * Is with tutor ?
     *
     * @return bool
     */
    public function isWithTutor()
    {
        return $this->withTutor;
    }

    /**
     * Set with tutor.
     *
     * @param bool $withTutor
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setWithTutor($withTutor)
    {
        $this->withTutor = $withTutor;

        return $this;
    }

    /**
     * Get secondary resources.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSecondaryResources()
    {
        return $this->secondaryResources;
    }

    /**
     * Add a secondary resource.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resource
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function addSecondaryResource(ResourceNode $resource)
    {
        if (!$this->secondaryResources->contains($resource)) {
            $this->secondaryResources->add($resource);
        }

        return $this;
    }

    /**
     * Remove a secondary resource.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resource
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function removeSecondaryResource(ResourceNode $resource)
    {
        if ($this->secondaryResources->contains($resource)) {
            $this->secondaryResources->removeElement($resource);
        }

        return $this;
    }

    /**
     * Get evaluation type.
     *
     * @return string
     */
    public function getEvaluationType()
    {
        return $this->evaluationType;
    }

    /**
     * Set evaluation type.
     *
     * @param string $evaluationType
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setEvaluationType($evaluationType)
    {
        $this->evaluationType = $evaluationType;

        return $this;
    }

    /**
     * Set rules.
     *
     * @param \Claroline\CoreBundle\Rule\Entity\Rule[]|\Doctrine\Common\Collections\ArrayCollection $rules
     *
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Get rules.
     *
     * @return \Claroline\CoreBundle\Rule\Entity\Rule[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getRules()
    {
        return $this->rules;
    }
}

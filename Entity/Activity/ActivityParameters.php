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

use Claroline\CoreBundle\Rule\Rulable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_activity_parameters")
 */
class ActivityParameters extends Rulable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\Activity",
     *     inversedBy="parameters"
     * )
     * @ORM\JoinColumn(name="activity_id", onDelete="CASCADE", nullable=true)
     */
    protected $activity;

    /**
     * @ORM\Column(name="max_duration", type="integer", nullable=true)
     */
    protected $maxDuration;

    /**
     * @ORM\Column(name="max_attempts", type="integer", nullable=true)
     */
    protected $maxAttempts;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode"
     * )
     * @ORM\JoinTable(
     *     name="claro_activity_secondary_resources",
     *     joinColumns={@ORM\JoinColumn(name="activity_parameters_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="resource_node_id", referencedColumnName="id")}
     * )
     */
    protected $secondaryResources;

    /**
     * @ORM\Column(name="evaluation_type", nullable=true)
     */
    protected $evaluationType;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityRule",
     *     mappedBy="activityParameters"
     * )
     */
    protected $rules;

    public function __construct()
    {
        $this->secondaryResources = new ArrayCollection();
        $this->rules = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getActivity()
    {
        return $this->activity;
    }

    public function setActivity($activity)
    {
        $this->activity = $activity;
    }

    public function getMaxDuration()
    {
        return $this->maxDuration;
    }

    public function setMaxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;
    }

    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }

    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }

    public function getSecondaryResources()
    {
        return $this->secondaryResources;
    }

    public function getEvaluationType()
    {
        return $this->evaluationType;
    }

    public function setEvaluationType($evaluationType)
    {
        $this->evaluationType = $evaluationType;
    }

    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    public function getRules()
    {
        return $this->rules;
    }
}

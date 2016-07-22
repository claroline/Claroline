<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_scorm_12_sco")
 */
class Scorm12Sco
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_user_min"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm12Resource",
     *     inversedBy="scos"
     * )
     * @ORM\JoinColumn(name="scorm_resource_id", onDelete="CASCADE", nullable=false)
     */
    protected $scormResource;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm12Sco",
     *     inversedBy="scoChildren"
     * )
     * @ORM\JoinColumn(name="sco_parent_id", onDelete="CASCADE", nullable=true)
     */
    protected $scoParent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm12Sco",
     *     mappedBy="scoParent"
     * )
     */
    protected $scoChildren;

    /**
     * @ORM\Column(name="entry_url", nullable=true)
     */
    protected $entryUrl;

    /**
     * @ORM\Column(name="scorm_identifier", nullable=false)
     * @Groups({"api_user_min"})
     */
    protected $identifier;

    /**
     * @ORM\Column(nullable=false, length=200)
     * @Groups({"api_user_min"})
     */
    protected $title;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"api_user_min"})
     */
    protected $visible;

    /**
     * @ORM\Column(nullable=true, length=1000)
     */
    protected $parameters;

    /**
     * @ORM\Column(nullable=true, length=200)
     */
    protected $prerequisites;

    /**
     * @ORM\Column(name="max_time_allowed", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("maxTimeAllowed")
     */
    protected $maxTimeAllowed;

    /**
     * @ORM\Column(name="time_limit_action", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("timeLimitAction")
     */
    protected $timeLimitAction;

    /**
     * @ORM\Column(name="launch_data", nullable=true, length=4096)
     * @Groups({"api_user_min"})
     * @SerializedName("launchData")
     */
    protected $launchData;

    /**
     * @ORM\Column(name="mastery_score", type="integer", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("masteryScore")
     */
    protected $masteryScore;

    /**
     * @ORM\Column(name="is_block", type="boolean", nullable=false)
     * @Groups({"api_user_min"})
     * @SerializedName("isBlock")
     */
    protected $isBlock;

    public function getId()
    {
        return $this->id;
    }

    public function getScormResource()
    {
        return $this->scormResource;
    }

    public function getScoParent()
    {
        return $this->scoParent;
    }

    public function getScoChildren()
    {
        return $this->scoChildren;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getPrerequisites()
    {
        return $this->prerequisites;
    }

    public function getMaxTimeAllowed()
    {
        return $this->maxTimeAllowed;
    }

    public function getTimeLimitAction()
    {
        return $this->timeLimitAction;
    }

    public function getLaunchData()
    {
        return $this->launchData;
    }

    public function getMasteryScore()
    {
        return $this->masteryScore;
    }

    public function getEntryUrl()
    {
        return $this->entryUrl;
    }

    public function getIsBlock()
    {
        return $this->isBlock;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setScormResource($scormResource)
    {
        $this->scormResource = $scormResource;
    }

    public function setScoParent($scoParent)
    {
        $this->scoParent = $scoParent;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function setPrerequisites($prerequisites)
    {
        $this->prerequisites = $prerequisites;
    }

    public function setMaxTimeAllowed($maxTimeAllowed)
    {
        $this->maxTimeAllowed = $maxTimeAllowed;
    }

    public function setTimeLimitAction($timeLimitAction)
    {
        $this->timeLimitAction = $timeLimitAction;
    }

    public function setLaunchData($launchData)
    {
        $this->launchData = $launchData;
    }

    public function setMasteryScore($masteryScore)
    {
        $this->masteryScore = $masteryScore;
    }

    public function setEntryUrl($entryUrl)
    {
        $this->entryUrl = $entryUrl;
    }

    public function setIsBlock($isBlock)
    {
        $this->isBlock = $isBlock;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }
}

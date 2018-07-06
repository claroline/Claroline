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

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_scorm_sco")
 */
class Sco
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm",
     *     inversedBy="scos",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="scorm_id", onDelete="CASCADE", nullable=false)
     */
    protected $scorm;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ScormBundle\Entity\Sco",
     *     inversedBy="scoChildren"
     * )
     * @ORM\JoinColumn(name="sco_parent_id", onDelete="CASCADE", nullable=true)
     */
    protected $scoParent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ScormBundle\Entity\Sco",
     *     mappedBy="scoParent"
     * )
     */
    protected $scoChildren;

    /**
     * @ORM\Column(name="entry_url", nullable=true)
     */
    protected $entryUrl;

    /**
     * @ORM\Column(nullable=false)
     */
    protected $identifier;

    /**
     * @ORM\Column(nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $visible;

    /**
     * @ORM\Column(name="sco_parameters", type="text", nullable=true)
     */
    protected $parameters;

    /**
     * @ORM\Column(name="launch_data", type="text", nullable=true)
     */
    protected $launchData;

    /**
     * @ORM\Column(name="max_time_allowed", nullable=true)
     */
    protected $maxTimeAllowed;

    /**
     * @ORM\Column(name="time_limit_action", nullable=true)
     */
    protected $timeLimitAction;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $block;

    /**
     * Score to pass for Scorm 1.2.
     *
     * @ORM\Column(name="score_int", type="integer", nullable=true)
     */
    protected $scoreToPassInt;

    /**
     * Score to pass for Scorm 2004.
     *
     * @ORM\Column(name="score_decimal", type="decimal", precision=10, scale=7, nullable=true)
     */
    protected $scoreToPassDecimal;

    /**
     * For Scorm 2004 only.
     *
     * @ORM\Column(name="completion_threshold", type="decimal", precision=10, scale=7, nullable=true)
     */
    protected $completionThreshold;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(nullable=true)
     */
    protected $prerequisites;

    public function __construct()
    {
        $this->refreshUuid();
        $this->scoChildren = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getScorm()
    {
        return $this->scorm;
    }

    public function setScorm(Scorm $scorm)
    {
        $this->scorm = $scorm;
    }

    public function getScoParent()
    {
        return $this->scoParent;
    }

    public function setScoParent(Sco $scoParent = null)
    {
        $this->scoParent = $scoParent;
    }

    public function getScoChildren()
    {
        return $this->scoChildren;
    }

    public function setScoChildren($scoChildren)
    {
        $this->scoChildren = $scoChildren;
    }

    public function getEntryUrl()
    {
        return $this->entryUrl;
    }

    public function setEntryUrl($entryUrl)
    {
        $this->entryUrl = $entryUrl;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getLaunchData()
    {
        return $this->launchData;
    }

    public function setLaunchData($launchData)
    {
        $this->launchData = $launchData;
    }

    public function getMaxTimeAllowed()
    {
        return $this->maxTimeAllowed;
    }

    public function setMaxTimeAllowed($maxTimeAllowed)
    {
        $this->maxTimeAllowed = $maxTimeAllowed;
    }

    public function getTimeLimitAction()
    {
        return $this->timeLimitAction;
    }

    public function setTimeLimitAction($timeLimitAction)
    {
        $this->timeLimitAction = $timeLimitAction;
    }

    public function isBlock()
    {
        return $this->block;
    }

    public function setBlock($block)
    {
        $this->block = $block;
    }

    public function getScoreToPass()
    {
        if (Scorm::SCORM_2004 === $this->scorm->getVersion()) {
            return $this->scoreToPassDecimal;
        } else {
            return $this->scoreToPassInt;
        }
    }

    public function setScoreToPass($scoreToPass)
    {
        if (Scorm::SCORM_2004 === $this->scorm->getVersion()) {
            $this->setScoreToPassDecimal($scoreToPass);
        } else {
            $this->setScoreToPassInt($scoreToPass);
        }
    }

    public function getScoreToPassInt()
    {
        return $this->scoreToPassInt;
    }

    public function setScoreToPassInt($scoreToPassInt)
    {
        $this->scoreToPassInt = $scoreToPassInt;
    }

    public function getScoreToPassDecimal()
    {
        return $this->scoreToPassDecimal;
    }

    public function setScoreToPassDecimal($scoreToPassDecimal)
    {
        $this->scoreToPassDecimal = $scoreToPassDecimal;
    }

    public function getCompletionThreshold()
    {
        return $this->completionThreshold;
    }

    public function setCompletionThreshold($completionThreshold)
    {
        $this->completionThreshold = $completionThreshold;
    }

    public function getPrerequisites()
    {
        return $this->prerequisites;
    }

    public function setPrerequisites($prerequisites)
    {
        $this->prerequisites = $prerequisites;
    }
}

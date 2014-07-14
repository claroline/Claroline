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

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_scorm_2004_sco")
 */
class Scorm2004Sco
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm2004Resource",
     *     inversedBy="scos"
     * )
     * @ORM\JoinColumn(name="scorm_resource_id", onDelete="CASCADE", nullable=false)
     */
    protected $scormResource;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm2004Sco",
     *     inversedBy="scoChildren"
     * )
     * @ORM\JoinColumn(name="sco_parent_id", onDelete="CASCADE", nullable=true)
     */
    protected $scoParent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm2004Sco",
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
     */
    protected $identifier;

    /**
     * @ORM\Column(nullable=false, length=200)
     */
    protected $title;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $visible;

    /**
     * @ORM\Column(nullable=true, length=1000)
     */
    protected $parameters;

    /**
     * @ORM\Column(name="time_limit_action", nullable=true)
     */
    protected $timeLimitAction;

    /**
     * @ORM\Column(name="launch_data", nullable=true, length=4000)
     */
    protected $launchData;

    /**
     * @ORM\Column(name="is_block", type="boolean", nullable=false)
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

    public function getEntryUrl()
    {
        return $this->entryUrl;
    }

    public function getIdentifier()
    {
        return $this->identifier;
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

    public function getTimeLimitAction()
    {
        return $this->timeLimitAction;
    }

    public function getLaunchData()
    {
        return $this->launchData;
    }

    public function getIsBlock()
    {
        return $this->isBlock;
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

    public function setScoChildren($scoChildren)
    {
        $this->scoChildren = $scoChildren;
    }

    public function setEntryUrl($entryUrl)
    {
        $this->entryUrl = $entryUrl;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
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

    public function setTimeLimitAction($timeLimitAction)
    {
        $this->timeLimitAction = $timeLimitAction;
    }

    public function setLaunchData($launchData)
    {
        $this->launchData = $launchData;
    }

    public function setIsBlock($isBlock)
    {
        $this->isBlock = $isBlock;
    }
}

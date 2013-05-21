<?php

namespace Claroline\ScormBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_scorm")
 */
class Scorm extends AbstractResource
{
    /**
     * @ORM\Column(type="string", length=36, name="hash_name")
     */
    protected $hashName;

    /**
     * @ORM\Column(name="mastery_score", type="integer", nullable=true)
     */
    protected $masteryScore;

    /**
     * @ORM\Column(name="launch_data", type="string", length=255, nullable=true)
     */
    protected $launchData;

    /**
     * @ORM\Column(name="entry_url", type="string", length=255, nullable=false)
     */
    protected $entryUrl;

    public function getHashName()
    {
        return $this->hashName;
    }

    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }

    public function getMasteryScore()
    {
        return $this->masteryScore;
    }

    public function setMasteryScore($masteryScore)
    {
        $this->masteryScore = $masteryScore;
    }

    public function getLaunchData()
    {
        return $this->launchData;
    }

    public function setLaunchData($launchData)
    {
        $this->launchData = $launchData;
    }

    public function getEntryUrl()
    {
        return $this->entryUrl;
    }

    public function setEntryUrl($entryUrl)
    {
        $this->entryUrl = $entryUrl;
    }
}
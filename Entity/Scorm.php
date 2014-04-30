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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\ScormBundle\Repository\ScormRepository")
 * @ORM\Table(name="claro_scorm")
 */
class Scorm extends AbstractResource
{
    /**
     * @ORM\Column(name="hash_name", length=50)
     */
    protected $hashName;

    /**
     * @ORM\Column(name="mastery_score", type="integer", nullable=true)
     */
    protected $masteryScore;

    /**
     * @ORM\Column(name="launch_data", nullable=true)
     */
    protected $launchData;

    /**
     * @ORM\Column(name="entry_url")
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
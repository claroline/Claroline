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
 * @ORM\Entity(repositoryClass="Claroline\ScormBundle\Repository\Scorm2004ResourceRepository")
 * @ORM\Table(name="claro_scorm_2004_resource")
 */
class Scorm2004Resource extends AbstractResource implements ScormResource
{
    /**
     * @ORM\Column(name="hash_name")
     */
    protected $hashName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm2004Sco",
     *     mappedBy="scormResource"
     * )
     */
    protected $scos;

    /**
     * @ORM\Column(name="hide_top_bar", type="boolean", options={"default" = 0})
     */
    protected $hideTopBar = false;

    /**
     * @ORM\Column(name="exit_mode", type="integer")
     */
    protected $exitMode = self::WORKSPACE_OPEN;

    public function getHashName()
    {
        return $this->hashName;
    }

    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
    }

    public function getScos()
    {
        return $this->scos;
    }

    public function getHideTopBar()
    {
        return $this->hideTopBar;
    }

    public function setHideTopBar($hideTopBar)
    {
        $this->hideTopBar = $hideTopBar;
    }

    public function getExitMode()
    {
        return $this->exitMode;
    }

    public function setExitMode($exitMode)
    {
        $this->exitMode = $exitMode;
    }
}

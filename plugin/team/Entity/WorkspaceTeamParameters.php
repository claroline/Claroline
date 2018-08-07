<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_team_parameters")
 * @ORM\Entity
 */
class WorkspaceTeamParameters
{
    use UuidTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $workspace;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     */
    protected $selfRegistration = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     */
    protected $selfUnregistration = false;

    /**
     * @ORM\Column(name="is_public", type="boolean")
     */
    protected $isPublic = false;

    /**
     * @ORM\Column(name="max_teams", type="integer", nullable=true)
     */
    protected $maxTeams;

    /**
     * @ORM\Column(name="dir_deletable", type="boolean", options={"default" = 0})
     */
    protected $dirDeletable = false;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function isSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function isSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function isPublic()
    {
        return $this->isPublic;
    }

    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    public function getMaxTeams()
    {
        return $this->maxTeams;
    }

    public function setMaxTeams($maxTeams)
    {
        $this->maxTeams = $maxTeams;
    }

    public function isDirDeletable()
    {
        return $this->dirDeletable;
    }

    public function setDirDeletable($dirDeletable)
    {
        $this->dirDeletable = $dirDeletable;
    }
}

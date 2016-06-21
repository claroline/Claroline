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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_team_parameters")
 * @ORM\Entity(repositoryClass="Claroline\TeamBundle\Repository\WorkspaceTeamParametersRepository")
 */
class WorkspaceTeamParameters
{
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
    protected $selfRegistration;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     */
    protected $selfUnregistration;

    /**
     * @ORM\Column(name="is_public", type="boolean")
     */
    protected $isPublic;

    /**
     * @ORM\Column(name="max_teams", type="integer", nullable=true)
     */
    protected $maxTeams;

    public function getId()
    {
        return $this->id;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    public function getIsPublic()
    {
        return $this->isPublic;
    }

    public function getMaxTeams()
    {
        return $this->maxTeams;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    public function setMaxTeams($maxTeams)
    {
        $this->maxTeams = $maxTeams;
    }
}

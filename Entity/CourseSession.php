<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseSessionRepository")
 * @ORM\Table(name="claro_cursusbundle_course_session")
 */
class CourseSession
{
    const SESSION_NOT_STARTED = 0;
    const SESSION_OPEN = 1;
    const SESSION_CLOSED = 2;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Course",
     *     inversedBy="sessions"
     * )
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     */
    protected $course;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=false, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinColumn(name="user_role_id", nullable=true, onDelete="SET NULL")
     */
    protected $userRole;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinColumn(name="manager_role_id", nullable=true, onDelete="SET NULL")
     */
    protected $managerRole;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Cursus"
     * )
     * @ORM\JoinColumn(name="cursus_id", nullable=true, onDelete="SET NULL")
     */
    protected $cursus;

    /**
     * @ORM\Column(name="session_status", type="integer")
     */
    protected $sessionStatus = 0;

    /**
     * @ORM\Column(name="default_session", type="boolean")
     */
    protected $defaultSession = false;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    protected $creationDate;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function setCourse(Course $course)
    {
        $this->course = $course;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getCursus()
    {
        return $this->cursus;
    }

    public function setCursus(Cursus $cursus)
    {
        $this->cursus = $cursus;
    }

    public function getSessionStatus()
    {
        return $this->sessionStatus;
    }

    public function setSessionStatus($sessionStatus)
    {
        $this->sessionStatus = $sessionStatus;
    }

    public function getUserRole()
    {
        return $this->userRole;
    }

    public function setUserRole(Role $userRole)
    {
        $this->userRole = $userRole;
    }

    public function getManagerRole()
    {
        return $this->managerRole;
    }

    public function setManagerRole(Role $managerRole)
    {
        $this->managerRole = $managerRole;
    }

    public function isDefaultSession()
    {
        return $this->defaultSession;
    }

    public function setDefaultSession($defaultSession)
    {
        $this->defaultSession = $defaultSession;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
}

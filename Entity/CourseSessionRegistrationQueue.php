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

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseSessionRegistrationQueueRepository")
 * @ORM\Table(
 *     name="claro_cursusbundle_course_session_registration_queue",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="session_queue_unique_session_user", columns={"session_id", "user_id"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"session", "user"})
 */
class CourseSessionRegistrationQueue
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\CourseSession")
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     */
    protected $session;

    /**
     * @ORM\Column(name="application_date", type="datetime")
     */
    protected $applicationDate;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function setSession(CourseSession $session)
    {
        $this->session = $session;
    }

    public function getApplicationDate()
    {
        return $this->applicationDate;
    }

    public function setApplicationDate($applicationDate)
    {
        $this->applicationDate = $applicationDate;
    }
}

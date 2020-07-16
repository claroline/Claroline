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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_cursusbundle_course_session_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="cursus_user_unique_course_session_user",
 *             columns={"session_id", "user_id", "user_type"}
 *         )
 *     }
 * )
 */
class CourseSessionUser
{
    use Id;
    use Uuid;

    const TYPE_LEARNER = 0;
    const TYPE_TEACHER = 1;
    const TYPE_PENDING_LEARNER = 2;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     inversedBy="sessionUsers"
     * )
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     *
     * @var CourseSession
     */
    protected $session;

    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $registrationDate;

    /**
     * @ORM\Column(name="user_type", type="integer", nullable=false)
     */
    protected $userType = self::TYPE_LEARNER;

    public function __construct()
    {
        $this->refreshUuid();
        $this->registrationDate = new \DateTime();
    }

    /**
     * @return User
     */
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

    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    }

    public function getUserType()
    {
        return $this->userType;
    }

    public function setUserType($userType)
    {
        $this->userType = $userType;
    }
}

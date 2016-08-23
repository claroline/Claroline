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
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseSessionUserRepository")
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
    const LEARNER = 0;
    const TEACHER = 1;
    const PENDING_LEARNER = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_user_min"})
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     inversedBy="sessionUsers"
     * )
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $session;

    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=false)
     * @Groups({"api_cursus", "api_user_min"})
     * @SerializedName("registrationDate")
     */
    protected $registrationDate;

    /**
     * @ORM\Column(name="user_type", type="integer", nullable=false)
     * @Groups({"api_cursus", "api_user_min"})
     * @SerializedName("userType")
     */
    protected $userType = self::LEARNER;

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

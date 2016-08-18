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
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\CourseSession")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $session;

    /**
     * @ORM\Column(name="application_date", type="datetime")
     * @Groups({"api_cursus", "api_user_min"})
     * @SerializedName("applicationDate")
     */
    protected $applicationDate;

    /**
     * @ORM\Column(name="queue_status", type="integer")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $status = CourseRegistrationQueue::WAITING;

    /**
     * @ORM\Column(name="validation_date", nullable=true, type="datetime")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $validationDate;

    /**
     * @ORM\Column(name="user_validation_date", nullable=true, type="datetime")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $userValidationDate;

    /**
     * @ORM\Column(name="validator_validation_date", nullable=true, type="datetime")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $validatorValidationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="validator_id", nullable=true, onDelete="SET NULL")
     */
    protected $validator;

    /**
     * @ORM\Column(name="organization_validation_date", nullable=true, type="datetime")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $organizationValidationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="organization_admin_id", nullable=true, onDelete="SET NULL")
     */
    protected $organizationAdmin;

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

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getValidationDate()
    {
        return $this->validationDate;
    }

    public function setValidationDate($validationDate)
    {
        $this->validationDate = $validationDate;
    }

    public function getUserValidationDate()
    {
        return $this->userValidationDate;
    }

    public function setUserValidationDate($userValidationDate)
    {
        $this->userValidationDate = $userValidationDate;
    }

    public function getValidatorValidationDate()
    {
        return $this->validatorValidationDate;
    }

    public function setValidatorValidationDate($validatorValidationDate)
    {
        $this->validatorValidationDate = $validatorValidationDate;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function setValidator(User $validator = null)
    {
        $this->validator = $validator;
    }

    public function getOrganizationValidationDate()
    {
        return $this->organizationValidationDate;
    }

    public function setOrganizationValidationDate($organizationValidationDate)
    {
        $this->organizationValidationDate = $organizationValidationDate;
    }

    public function getOrganizationAdmin()
    {
        return $this->organizationAdmin;
    }

    public function setOrganizationAdmin(User $organizationAdmin = null)
    {
        $this->organizationAdmin = $organizationAdmin;
    }
}

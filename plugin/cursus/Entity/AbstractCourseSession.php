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

use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class AbstractCourseSession
{
    use Poster;
    use Thumbnail;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     */
    protected $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     */
    protected $workspace;

    /**
     * @ORM\Column(name="public_registration", type="boolean")
     */
    protected $publicRegistration = false;

    /**
     * @ORM\Column(name="public_unregistration", type="boolean")
     */
    protected $publicUnregistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     */
    protected $registrationValidation = false;

    /**
     * @ORM\Column(name="user_validation", type="boolean")
     */
    protected $userValidation = false;

    /**
     * @ORM\Column(name="organization_validation", type="boolean")
     */
    protected $organizationValidation = false;

    /**
     * @ORM\Column(name="max_users", nullable=true, type="integer")
     */
    protected $maxUsers;

    /**
     * @ORM\Column(name="display_order", type="integer", options={"default" = 500})
     */
    protected $displayOrder = 500;

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function getPublicRegistration()
    {
        return $this->publicRegistration;
    }

    public function setPublicRegistration($publicRegistration)
    {
        $this->publicRegistration = $publicRegistration;
    }

    public function getPublicUnregistration()
    {
        return $this->publicUnregistration;
    }

    public function setPublicUnregistration($publicUnregistration)
    {
        $this->publicUnregistration = $publicUnregistration;
    }

    public function getRegistrationValidation()
    {
        return $this->registrationValidation;
    }

    public function setRegistrationValidation($registrationValidation)
    {
        $this->registrationValidation = $registrationValidation;
    }

    public function getUserValidation()
    {
        return $this->userValidation;
    }

    public function setUserValidation($userValidation)
    {
        $this->userValidation = $userValidation;
    }

    public function getOrganizationValidation()
    {
        return $this->organizationValidation;
    }

    public function setOrganizationValidation($organizationValidation)
    {
        $this->organizationValidation = $organizationValidation;
    }

    public function hasValidation()
    {
        return $this->registrationValidation || $this->userValidation || $this->organizationValidation;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
    }
}

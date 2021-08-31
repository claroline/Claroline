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

use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Order;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\AppBundle\Entity\Restriction\Hidden;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class AbstractTraining
{
    use Id;
    use Uuid;
    use Code;
    use Description;
    use Hidden;
    use Order;
    use CreatedAt;
    use UpdatedAt;
    use Creator;
    use Poster;
    use Thumbnail;

    /**
     * @ORM\Column(name="course_name")
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    protected $plainDescription;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     */
    protected $workspace;

    /**
     * @ORM\Column(name="public_registration", type="boolean")
     */
    protected $publicRegistration = false;

    /**
     * @ORM\Column(name="auto_registration", type="boolean")
     */
    protected $autoRegistration = false;

    /**
     * @ORM\Column(name="public_unregistration", type="boolean")
     */
    protected $publicUnregistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     */
    protected $registrationValidation = false;

    /**
     * @ORM\Column(name="registration_mail", type="boolean")
     */
    protected $registrationMail = false;

    /**
     * @ORM\Column(name="user_validation", type="boolean")
     */
    protected $userValidation = false;

    /**
     * Enables the waiting list for the training.
     *
     * @ORM\Column(name="pending_registrations", type="boolean")
     */
    protected $pendingRegistrations = false;

    /**
     * @ORM\Column(name="max_users", nullable=true, type="integer")
     */
    protected $maxUsers;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @var float
     */
    protected $price = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $priceDescription = null;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPlainDescription(): ?string
    {
        return $this->plainDescription;
    }

    public function setPlainDescription(string $description = null)
    {
        $this->plainDescription = $description;
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

    public function getAutoRegistration(): bool
    {
        return $this->autoRegistration;
    }

    public function setAutoRegistration(bool $autoRegistration)
    {
        $this->autoRegistration = $autoRegistration;
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

    public function getRegistrationMail(): bool
    {
        return $this->registrationMail;
    }

    public function setRegistrationMail(bool $mail)
    {
        $this->registrationMail = $mail;
    }

    public function getUserValidation()
    {
        return $this->userValidation;
    }

    public function setUserValidation($userValidation)
    {
        $this->userValidation = $userValidation;
    }

    public function hasValidation()
    {
        return $this->registrationValidation || $this->userValidation;
    }

    public function getPendingRegistrations(): bool
    {
        return $this->pendingRegistrations;
    }

    public function setPendingRegistrations(bool $pendingRegistrations)
    {
        $this->pendingRegistrations = $pendingRegistrations;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price = null)
    {
        $this->price = $price;
    }

    public function getPriceDescription(): ?string
    {
        return $this->priceDescription;
    }

    public function setPriceDescription(string $description = null)
    {
        $this->priceDescription = $description;
    }
}

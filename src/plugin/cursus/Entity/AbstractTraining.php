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
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\Order;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\AppBundle\Entity\Restriction\Hidden;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class AbstractTraining
{
    use Id;
    use Uuid;
    use Code;
    use Name;
    use Description;
    use Hidden;
    use Order;
    use CreatedAt;
    use UpdatedAt;
    use Creator;
    use Poster;
    use Thumbnail;

    /**
     * @ORM\Column(nullable=true)
     */
    protected ?string $plainDescription = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     */
    protected ?Workspace $workspace = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(name="learner_role_id", nullable=true, onDelete="SET NULL")
     */
    protected ?Role $learnerRole = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(name="tutor_role_id", nullable=true, onDelete="SET NULL")
     */
    protected ?Role $tutorRole = null;

    /**
     * @ORM\Column(name="public_registration", type="boolean")
     */
    protected bool $publicRegistration = false;

    /**
     * @ORM\Column(name="auto_registration", type="boolean")
     */
    protected bool $autoRegistration = false;

    /**
     * @ORM\Column(name="public_unregistration", type="boolean")
     */
    protected bool $publicUnregistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     */
    protected bool $registrationValidation = false;

    /**
     * @ORM\Column(name="registration_mail", type="boolean")
     */
    protected bool $registrationMail = false;

    /**
     * @ORM\Column(name="user_validation", type="boolean")
     */
    protected bool $userValidation = false;

    /**
     * Enables the waiting list for the training.
     *
     * @ORM\Column(name="pending_registrations", type="boolean")
     */
    protected bool $pendingRegistrations = false;

    /**
     * @ORM\Column(name="max_users", nullable=true, type="integer")
     */
    protected ?int $maxUsers = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected ?float $price = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $priceDescription = null;

    public function getPlainDescription(): ?string
    {
        return $this->plainDescription;
    }

    public function setPlainDescription(string $description = null)
    {
        $this->plainDescription = $description;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function getLearnerRole(): ?Role
    {
        return $this->learnerRole;
    }

    public function setLearnerRole(?Role $learnerRole = null): void
    {
        $this->learnerRole = $learnerRole;
    }

    public function getTutorRole(): ?Role
    {
        return $this->tutorRole;
    }

    public function setTutorRole(?Role $tutorRole = null): void
    {
        $this->tutorRole = $tutorRole;
    }

    public function getPublicRegistration(): bool
    {
        return $this->publicRegistration;
    }

    public function setPublicRegistration(bool $publicRegistration): void
    {
        $this->publicRegistration = $publicRegistration;
    }

    public function getAutoRegistration(): bool
    {
        return $this->autoRegistration;
    }

    public function setAutoRegistration(bool $autoRegistration): void
    {
        $this->autoRegistration = $autoRegistration;
    }

    public function getPublicUnregistration(): bool
    {
        return $this->publicUnregistration;
    }

    public function setPublicUnregistration(bool $publicUnregistration): void
    {
        $this->publicUnregistration = $publicUnregistration;
    }

    public function getRegistrationValidation(): bool
    {
        return $this->registrationValidation;
    }

    public function setRegistrationValidation(bool $registrationValidation): void
    {
        $this->registrationValidation = $registrationValidation;
    }

    public function getRegistrationMail(): bool
    {
        return $this->registrationMail;
    }

    public function setRegistrationMail(bool $mail): void
    {
        $this->registrationMail = $mail;
    }

    public function getUserValidation(): bool
    {
        return $this->userValidation;
    }

    public function setUserValidation(bool $userValidation): void
    {
        $this->userValidation = $userValidation;
    }

    public function hasValidation(): bool
    {
        return $this->registrationValidation || $this->userValidation;
    }

    public function getPendingRegistrations(): bool
    {
        return $this->pendingRegistrations;
    }

    public function setPendingRegistrations(bool $pendingRegistrations): void
    {
        $this->pendingRegistrations = $pendingRegistrations;
    }

    public function getMaxUsers(): ?int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(?int $maxUsers): void
    {
        $this->maxUsers = $maxUsers;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price = null): void
    {
        $this->price = $price;
    }

    public function getPriceDescription(): ?string
    {
        return $this->priceDescription;
    }

    public function setPriceDescription(?string $description = null): void
    {
        $this->priceDescription = $description;
    }
}

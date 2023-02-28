<?php

namespace Claroline\CursusBundle\Entity\Registration;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractUserRegistration extends AbstractRegistration
{
    /**
     * The registration request has been confirmed by the user.
     *
     * @ORM\Column(type="boolean")
     */
    protected bool $confirmed = false;

    /**
     * The registration request has been validated by a manager.
     * It is false when the registration requires manual validation or if there is no more seats to validate the registration.
     *
     * @ORM\Column(type="boolean")
     */
    protected bool $validated = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    protected ?User $user = null;

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): void
    {
        $this->confirmed = $confirmed;
    }

    public function isValidated(): bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): void
    {
        $this->validated = $validated;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}

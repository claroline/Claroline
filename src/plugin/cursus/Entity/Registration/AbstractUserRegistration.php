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
     *
     * @var bool
     */
    protected $confirmed = false;

    /**
     * The registration request has been validated by a manager.
     * It is false when the registration requires manual validation or if their is no more seats to validate the registration.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $validated = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    protected $user;

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed)
    {
        $this->confirmed = $confirmed;
    }

    public function isValidated(): bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated)
    {
        $this->validated = $validated;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}

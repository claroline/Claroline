<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity\Registration;

use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_cursusbundle_course_session_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="training_session_unique_user", columns={"session_id", "user_id"})
 *     }
 * )
 */
class SessionUser extends AbstractUserRegistration
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Session")
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     *
     * @var Session
     */
    private $session;

    /**
     * The registration has to be managed by another service
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $managed = false;

    /**
     * The registration has to be refused by human resource
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $refused = false;

    public function getSession(): Session
    {
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function isManaged(): bool
    {
        return $this->managed;
    }

    public function setManaged(bool $managed)
    {
        $this->managed = $managed;
    }

    public function isRefused(): bool
    {
        return $this->refused;
    }

    public function setRefused(bool $refused)
    {
        $this->refused = $refused;
    }
}

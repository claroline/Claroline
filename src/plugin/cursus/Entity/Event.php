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
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Template\Template;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\EventRepository")
 * @ORM\Table(name="claro_cursusbundle_session_event")
 */
class Event extends AbstractPlanned
{
    use Code;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Session",
     *     inversedBy="events"
     * )
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     *
     * @var Session
     */
    private $session;

    /**
     * @ORM\Column(name="max_users", nullable=true, type="integer")
     *
     * @var int
     */
    private $maxUsers;

    /**
     * @ORM\Column(name="registration_type", type="integer", nullable=false, options={"default" = 0})
     *
     * @var int
     */
    private $registrationType = Session::REGISTRATION_AUTO;

    /**
     * @ORM\Column(name="registration_mail", type="boolean")
     *
     * @var bool
     */
    private $registrationMail = true;

    /**
     * Template used to print the presence of a User.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Template\Template")
     * @ORM\JoinColumn(name="presence_template_id", nullable=true, onDelete="SET NULL")
     *
     * @var Template
     */
    private $presenceTemplate;

    public static function getType(): string
    {
        return 'training_event';
    }

    public function getSession()
    {
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getRegistrationType()
    {
        return $this->registrationType;
    }

    public function setRegistrationType($registrationType)
    {
        $this->registrationType = $registrationType;
    }

    public function getRegistrationMail(): bool
    {
        return $this->registrationMail;
    }

    public function setRegistrationMail(bool $mail)
    {
        $this->registrationMail = $mail;
    }

    public function getPresenceTemplate(): ?Template
    {
        return $this->presenceTemplate;
    }

    public function setPresenceTemplate(?Template $template = null)
    {
        $this->presenceTemplate = $template;
    }
}

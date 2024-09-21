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

use Doctrine\DBAL\Types\Types;
use Claroline\CursusBundle\Repository\EventRepository;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Template\Template;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_cursusbundle_session_event')]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event extends AbstractPlanned
{
    use Code;

    /**
     *
     *
     * @var Session
     */
    #[ORM\JoinColumn(name: 'session_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'events')]
    private ?Session $session = null;

    /**
     * @var int
     */
    #[ORM\Column(name: 'max_users', nullable: true, type: Types::INTEGER)]
    private $maxUsers;

    /**
     * @var int
     */
    #[ORM\Column(name: 'registration_type', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private $registrationType = Session::REGISTRATION_AUTO;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'registration_mail', type: Types::BOOLEAN)]
    private $registrationMail = true;

    /**
     * Template used to print the presence of a User.
     *
     *
     *
     * @var Template
     */
    #[ORM\JoinColumn(name: 'presence_template_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Template::class)]
    private ?Template $presenceTemplate = null;

    
    #[ORM\JoinColumn(name: 'invitation_template_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Template::class)]
    private ?Template $invitationTemplate = null;

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

    public function setPresenceTemplate(Template $template = null)
    {
        $this->presenceTemplate = $template;
    }

    public function getInvitationTemplate(): ?Template
    {
        return $this->invitationTemplate;
    }

    public function setInvitationTemplate(Template $template = null)
    {
        $this->invitationTemplate = $template;
    }
}

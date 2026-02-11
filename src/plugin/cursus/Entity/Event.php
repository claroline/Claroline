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

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CursusBundle\Finder\EventType;
use Claroline\CursusBundle\Repository\EventRepository;
use Claroline\TemplateBundle\Entity\Template;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_cursusbundle_session_event')]
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[CrudEntity(finderClass: EventType::class)]
class Event extends AbstractPlanned
{
    use Code;

    #[ORM\JoinColumn(name: 'session_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'events')]
    private ?Session $session = null;

    #[ORM\Column(name: 'max_users', type: Types::INTEGER, nullable: true)]
    private ?int $maxUsers = null;

    #[ORM\Column(name: 'registration_type', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $registrationType = Session::REGISTRATION_AUTO;

    #[ORM\Column(name: 'registration_mail', type: Types::BOOLEAN)]
    private bool $registrationMail = true;

    /**
     * Template used to print the presence of a User.
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

    public function getCourse(): ?Course
    {
        return $this->session->getCourse();
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    public function getMaxUsers(): ?int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(?int $maxUsers): void
    {
        $this->maxUsers = $maxUsers;
    }

    public function getRegistrationType(): int
    {
        return $this->registrationType;
    }

    public function setRegistrationType(int $registrationType): void
    {
        $this->registrationType = $registrationType;
    }

    public function getRegistrationMail(): bool
    {
        return $this->registrationMail;
    }

    public function setRegistrationMail(bool $mail): void
    {
        $this->registrationMail = $mail;
    }

    public function getPresenceTemplate(): ?Template
    {
        return $this->presenceTemplate;
    }

    public function setPresenceTemplate(?Template $template = null): void
    {
        $this->presenceTemplate = $template;
    }

    public function getInvitationTemplate(): ?Template
    {
        return $this->invitationTemplate;
    }

    public function setInvitationTemplate(?Template $template = null): void
    {
        $this->invitationTemplate = $template;
    }
}

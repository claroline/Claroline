<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Entity;

use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_event')]
#[ORM\Entity]
class Event extends AbstractPlanned
{
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class, cascade: ['persist'])]
    private ?Workspace $workspace = null;

    /**
     * Template used to send invitations to Users.
     */
    #[ORM\JoinColumn(name: 'invitation_template_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Template::class)]
    private ?Template $invitationTemplate = null;

    /**
     * @var Collection<int, EventInvitation>
     */
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\OneToMany(targetEntity: EventInvitation::class, mappedBy: 'event')]
    private Collection $eventInvitations;

    public function __construct()
    {
        parent::__construct();

        $this->eventInvitations = new ArrayCollection();
    }

    public static function getType(): string
    {
        return 'event';
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null): void
    {
        $this->workspace = $workspace;
    }

    public function addEventInvitation(EventInvitation $eventInvitation): void
    {
        $this->eventInvitations[] = $eventInvitation;
    }

    public function removeEventInvitation(EventInvitation $eventInvitation): void
    {
        $this->eventInvitations->removeElement($eventInvitation);
    }

    public function getEventInvitations(): Collection
    {
        return $this->eventInvitations;
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

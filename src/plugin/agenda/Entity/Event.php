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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_event")
 */
class Event extends AbstractPlanned
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\AgendaBundle\Entity\EventInvitation", mappedBy="event")
     * @ORM\JoinColumn(nullable=true)
     */
    private $eventInvitations;

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

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function addEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations[] = $eventInvitation;
    }

    public function removeEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations->removeElement($eventInvitation);
    }

    public function getEventInvitations()
    {
        return $this->eventInvitations;
    }
}

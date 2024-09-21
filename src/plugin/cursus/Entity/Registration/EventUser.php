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

use Claroline\CursusBundle\Repository\Registration\EventUserRepository;
use Claroline\CursusBundle\Entity\Event;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_cursusbundle_session_event_user')]
#[ORM\UniqueConstraint(name: 'training_event_unique_user', columns: ['event_id', 'user_id'])]
#[ORM\Entity(repositoryClass: EventUserRepository::class)]
class EventUser extends AbstractUserRegistration
{
    #[ORM\JoinColumn(name: 'event_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Event::class)]
    private ?Event $event = null;

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }
}

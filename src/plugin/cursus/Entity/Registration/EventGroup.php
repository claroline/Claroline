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

use Claroline\CursusBundle\Repository\Registration\EventGroupRepository;
use Claroline\CursusBundle\Entity\Event;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_cursusbundle_session_event_group')]
#[ORM\UniqueConstraint(name: 'training_event_unique_group', columns: ['event_id', 'group_id'])]
#[ORM\Entity(repositoryClass: EventGroupRepository::class)]
class EventGroup extends AbstractGroupRegistration
{
    /**
     *
     * @var Event
     */
    #[ORM\JoinColumn(name: 'event_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Event::class)]
    private $event;

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;
    }
}

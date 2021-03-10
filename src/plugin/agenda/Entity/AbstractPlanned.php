<?php

namespace Claroline\AgendaBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class AbstractPlanned
{
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\AgendaBundle\Entity\Event")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var Event
     */
    protected $event;

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;
    }
}

<?php

namespace Claroline\LogBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_log_message')]
#[ORM\Entity]
class MessageLog extends AbstractLog
{
    
    #[ORM\JoinColumn(name: 'receiver_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $receiver = null;

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }
}

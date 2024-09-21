<?php

namespace Claroline\NotificationBundle\Entity;

use DateTime;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_notification')]
#[ORM\Entity]
class Notification
{
    use Id;
    use Uuid;
    use CreatedAt;
    use Thumbnail;

    #[ORM\Column(nullable: true)]
    private ?string $message = null;

    
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    public function __construct()
    {
        $this->refreshUuid();
        $this->createdAt = new DateTime();
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}

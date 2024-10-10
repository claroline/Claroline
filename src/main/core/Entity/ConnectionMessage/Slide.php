<?php

namespace Claroline\CoreBundle\Entity\ConnectionMessage;

use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_connection_message_slide')]
#[ORM\Entity]
class Slide
{
    use Id;
    use Uuid;
    use Poster;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?string $title = null;

    #[ORM\Column(name: 'slide_order', type: Types::INTEGER)]
    private int $order = 0;

    #[ORM\JoinColumn(name: 'message_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ConnectionMessage::class, inversedBy: 'slides')]
    private ?ConnectionMessage $message = null;

    #[ORM\Column(name: 'shortcuts', type: Types::JSON, nullable: true)]
    private array $shortcuts = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function getMessage(): ?ConnectionMessage
    {
        return $this->message;
    }

    public function setMessage(?ConnectionMessage $message = null): void
    {
        $this->message = $message;
    }

    public function getShortcuts(): ?array
    {
        return $this->shortcuts;
    }

    public function setShortcuts(array $shortcuts): void
    {
        $this->shortcuts = $shortcuts;
    }
}

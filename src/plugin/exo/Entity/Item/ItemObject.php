<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Resource on which the Item is referred.
 */
#[ORM\Table(name: 'ujm_object_question')]
#[ORM\Entity]
class ItemObject
{
    use Order;
    use Uuid;
    use Id;

    /**
     * Owning Item.
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'objects')]
    private ?Item $question = null;

    /**
     * The mime type of the Item object.
     */
    #[ORM\Column('mime_type', type: Types::STRING)]
    private ?string $mimeType = null;

    #[ORM\Column(name: 'object_data', type: Types::TEXT)]
    private ?string $data = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setQuestion(?Item $question = null): void
    {
        $this->question = $question;
    }

    public function getQuestion(): ?Item
    {
        return $this->question;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }
}

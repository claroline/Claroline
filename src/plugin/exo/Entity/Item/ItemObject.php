<?php

namespace UJM\ExoBundle\Entity\Item;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Order;
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
     *
     * @var Item
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'objects')]
    private ?Item $question = null;

    /**
     * The mime type of the Item object.
     *
     *
     * @var string
     */
    #[ORM\Column('mime_type', type: Types::STRING)]
    private $mimeType;

    #[ORM\Column(name: 'object_data', type: Types::TEXT)]
    private $data;

    /**
     * ItemObject constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Set question.
     *
     * @param Item $question
     */
    public function setQuestion(Item $question = null)
    {
        $this->question = $question;
    }

    /**
     * Get question.
     *
     * @return Item
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Gets mime type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Sets mime type.
     *
     * @param $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Gets data.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets data.
     *
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}

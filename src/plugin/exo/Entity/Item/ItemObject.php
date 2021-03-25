<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Order;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Resource on which the Item is referred.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_object_question")
 */
class ItemObject
{
    use Order;
    use Uuid;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Owning Item.
     *
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Item\Item", inversedBy="objects")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $question;

    /**
     * The mime type of the Item object.
     *
     * @ORM\Column("mime_type", type="string")
     *
     * @var string
     */
    private $mimeType;

    /**
     * @ORM\Column(name="object_data", type="text")
     */
    private $data;

    /**
     * ItemObject constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

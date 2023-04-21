<?php

namespace Claroline\CoreBundle\Entity\ConnectionMessage;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Poster;
use Doctrine\ORM\Mapping as ORM;

/**
 * Slide.
 *
 * @ORM\Table(name="claro_connection_message_slide")
 * @ORM\Entity()
 */
class Slide
{
    use Id;
    use Uuid;
    use Poster;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $content;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="slide_order", type="integer")
     *
     * @var int
     */
    private $order;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage",
     *     inversedBy="slides"
     * )
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var ConnectionMessage
     */
    private $message;

    /**
     * @ORM\Column(name="shortcuts", type="json", nullable=true)
     *
     * @var array
     */
    private $shortcuts = [];

    /**
     * Slide constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Get connection message.
     *
     * @return ConnectionMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set connection message.
     *
     * @param ConnectionMessage $message
     */
    public function setMessage(ConnectionMessage $message = null)
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getShortcuts()
    {
        return $this->shortcuts;
    }

    public function setShortcuts(array $shortcuts)
    {
        $this->shortcuts = $shortcuts;
    }
}

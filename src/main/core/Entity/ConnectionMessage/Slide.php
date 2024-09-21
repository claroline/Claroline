<?php

namespace Claroline\CoreBundle\Entity\ConnectionMessage;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Poster;
use Doctrine\ORM\Mapping as ORM;

/**
 * Slide.
 */
#[ORM\Table(name: 'claro_connection_message_slide')]
#[ORM\Entity]
class Slide
{
    use Id;
    use Uuid;
    use Poster;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private $content;

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $title;

    /**
     * @var int
     */
    #[ORM\Column(name: 'slide_order', type: Types::INTEGER)]
    private $order;

    /**
     *
     * @var ConnectionMessage
     */
    #[ORM\JoinColumn(name: 'message_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ConnectionMessage::class, inversedBy: 'slides')]
    private ?ConnectionMessage $message = null;

    /**
     * @var array
     */
    #[ORM\Column(name: 'shortcuts', type: Types::JSON, nullable: true)]
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

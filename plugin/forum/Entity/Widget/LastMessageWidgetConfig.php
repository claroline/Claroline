<?php

namespace Claroline\ForumBundle\Entity\Widget;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\LastMessageWidgetConfigRepository")
 * @ORM\Table(name="claro_forum_last_message_widget_config")
 */
class LastMessageWidgetConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(name="widget_instance_id", onDelete="CASCADE", nullable=false, unique=true)
     */
    protected $widgetInstance;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_my_last_messages", type="boolean")
     */
    protected $displayMyLastMessages;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="forum_id", onDelete="SET NULL", nullable=true)
     */
    protected $forum;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return WidgetInstance
     */
    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return LastMessageWidgetConfig
     */
    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisplayMyLastMessages()
    {
        return $this->displayMyLastMessages;
    }

    /**
     * @param bool $displayMyLastMessages
     *
     * @return LastMessageWidgetConfig
     */
    public function setDisplayMyLastMessages($displayMyLastMessages)
    {
        $this->displayMyLastMessages = $displayMyLastMessages;

        return $this;
    }

    public function getForum()
    {
        return $this->forum;
    }

    public function setForum(ResourceNode $forum = null)
    {
        $this->forum = $forum;
    }
}

<?php

namespace Claroline\AnnouncementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_announcement")
 */
class Announcement
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(length=1023, nullable=true)
     */
    protected $content;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $announcer;

    /**
     * @ORM\Column(name="publication_date", type="datetime", nullable=true)
     */
    protected $publicationDate;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $visible;

    /**
     * @ORM\Column(name="visible_from", type="datetime", nullable=true)
     */
    protected $visibleFrom;

    /**
     * @ORM\Column(name="visible_until", type="datetime", nullable=true)
     */
    protected $visibleUntil;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $order;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="creator_id", onDelete="CASCADE", nullable=false)
     */
    protected $creator;


    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\AnnouncementBundle\Entity\AnnouncementAggregate",
     *     cascade={"persist"},
     *     inversedBy="announcements"
     * )
     * @ORM\JoinColumn(name="aggregate_id", onDelete="CASCADE", nullable=false)
     */
    protected $aggregate;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getAnnouncer()
    {
        return $this->announcer;
    }

    public function setAnnouncer($announcer)
    {
        $this->announcer = $announcer;
    }

    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function getVisibleFrom()
    {
        return $this->visibleFrom;
    }

    public function setVisibleFrom($visibleFrom)
    {
        $this->visibleFrom = $visibleFrom;
    }

    public function getVisibleUntil()
    {
        return $this->visibleUntil;
    }

    public function setVisibleUntil($visibleUntil)
    {
        $this->visibleUntil = $visibleUntil;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function getAggregator()
    {
        return $this->aggregator;
    }

    public function setAggregator($aggregator)
    {
        $this->aggregator = $aggregator;
    }
}
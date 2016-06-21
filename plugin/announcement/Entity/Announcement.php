<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\AnnouncementBundle\Repository\AnnouncementRepository")
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
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $announcer;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    protected $creationDate;

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
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="creator_id", onDelete="CASCADE", nullable=false)
     */
    protected $creator;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\AnnouncementBundle\Entity\AnnouncementAggregate",
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

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function getAggregate()
    {
        return $this->aggregate;
    }

    public function setAggregate($aggregate)
    {
        $this->aggregate = $aggregate;
    }
}

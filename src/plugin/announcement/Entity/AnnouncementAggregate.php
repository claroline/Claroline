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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Template\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_announcement_aggregate")
 */
class AnnouncementAggregate extends AbstractResource
{
    /**
     * The list of announces in the aggregate.
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\AnnouncementBundle\Entity\Announcement",
     *     mappedBy="aggregate",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection|Announcement[]
     */
    private $announcements;

    /**
     * Template used to print the presence of a User.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Template\Template")
     * @ORM\JoinColumn(name="announcement_template_id", nullable=true, onDelete="SET NULL")
     *
     * @var Template
     */
    private $announcementTemplate;

    /**
     * AnnouncementAggregate constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->announcements = new ArrayCollection();
    }

    /**
     * Get announcements.
     *
     * @return ArrayCollection|Announcement[]
     */
    public function getAnnouncements()
    {
        return $this->announcements;
    }

    public function getAnnouncementTemplate(): ?Template
    {
        return $this->announcementTemplate;
    }

    public function setAnnouncementTemplate(?Template $template = null): void
    {
        $this->announcementTemplate = $template;
    }
}

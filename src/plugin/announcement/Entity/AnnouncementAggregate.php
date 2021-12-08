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
}

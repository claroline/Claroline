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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_announcement_aggregate")
 */
class AnnouncementAggregate extends AbstractResource
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\AnnouncementBundle\Entity\Announcement",
     *     mappedBy="aggregate"
     * )
     */
    protected $announcements;

    public function getAnnouncements()
    {
        return $this->announcements;
    }
}

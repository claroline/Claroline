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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_announcements_send')]
#[ORM\Entity]
class AnnouncementSend
{
    use Id;
    use Uuid;

    #[ORM\JoinColumn(name: 'announcement_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\AnnouncementBundle\Entity\Announcement::class)]
    private ?Announcement $announcement = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $data = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setAnnouncement(Announcement $announcement): void
    {
        $this->announcement = $announcement;
    }

    public function getAnnouncement(): ?Announcement
    {
        return $this->announcement;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}

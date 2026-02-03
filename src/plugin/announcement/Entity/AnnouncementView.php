<?php

namespace Claroline\AnnouncementBundle\Entity;

use Claroline\AnnouncementBundle\Finder\AnnouncementViewType;
use Claroline\AnnouncementBundle\Repository\AnnouncementViewRepository;
use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\AbstractUserView;
use Doctrine\ORM\Mapping as ORM;
use Icap\LessonBundle\Entity\Chapter;

#[ORM\Table('claro_announcement_view')]
#[ORM\Entity(repositoryClass: AnnouncementViewRepository::class)]
#[CrudEntity(finderClass: AnnouncementViewType::class)]
class AnnouncementView extends AbstractUserView
{
    #[ORM\JoinColumn(name: 'announcement_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Announcement::class)]
    private ?Announcement $announcement = null;

    public function getSubject(): Chapter
    {
        return $this->announcement;
    }

    /** @param Chapter $subject */
    public function setSubject(object $subject): void
    {
        $this->announcement = $subject;
    }
}

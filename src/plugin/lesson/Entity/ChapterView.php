<?php

namespace Icap\LessonBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\AbstractUserView;
use Doctrine\ORM\Mapping as ORM;
use Icap\LessonBundle\Finder\ChapterViewType;
use Icap\LessonBundle\Repository\ChapterViewRepository;

#[ORM\Table('claro_chapter_view')]
#[ORM\Entity(repositoryClass: ChapterViewRepository::class)]
#[CrudEntity(finderClass: ChapterViewType::class)]
class ChapterView extends AbstractUserView
{
    #[ORM\JoinColumn(name: 'chapter_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Chapter::class)]
    private ?Chapter $chapter = null;

    public function getSubject(): Chapter
    {
        return $this->chapter;
    }

    /** @param Chapter $subject */
    public function setSubject(object $subject): void
    {
        $this->chapter = $subject;
    }
}

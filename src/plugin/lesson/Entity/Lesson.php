<?php

namespace Icap\LessonBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'icap__lesson')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Lesson extends AbstractResource
{
    /**
     * Show overview to users or directly start the lesson.
     */
    #[ORM\Column(name: 'show_overview', type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $showOverview = false;

    /**
     * Show pages meta (creator, last edition date) to users.
     */
    #[ORM\Column(name: 'show_meta', type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $showMeta = false;

    /**
     * Displays Next and Previous button to navigate between pages.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $navigation = false;

    /**
     * Display the summary opened as default.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $openSummary = false;

    /**
     * Numbering of the chapters.
     */
    #[ORM\Column]
    private string $numbering = 'none';

    /**
     * Pagination of the lesson.
     *   - none : All the chapters are displayed in the same page, one after another.
     *   - page : One page per chapter + all of its subchapters.
     *   - all  : One page per chapter.
     */
    #[ORM\Column]
    private string $pagination = 'all';

    #[ORM\OneToOne(targetEntity: Chapter::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'root_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Chapter $root = null;

    public function setShowOverview(bool $showOverview): void
    {
        $this->showOverview = $showOverview;
    }

    public function getShowOverview(): bool
    {
        return $this->showOverview;
    }

    public function setShowMeta(bool $showMeta): void
    {
        $this->showMeta = $showMeta;
    }

    public function getShowMeta(): bool
    {
        return $this->showMeta;
    }

    public function hasNavigation(): bool
    {
        return $this->navigation;
    }

    public function setNavigation(bool $navigation): void
    {
        $this->navigation = $navigation;
    }

    public function getOpenSummary(): bool
    {
        return $this->openSummary;
    }

    public function setOpenSummary(bool $openSummary): void
    {
        $this->openSummary = $openSummary;
    }

    public function getNumbering(): string
    {
        return $this->numbering;
    }

    public function setNumbering(?string $numbering): void
    {
        $this->numbering = $numbering;
    }

    public function getPagination(): string
    {
        return $this->pagination;
    }

    public function setPagination(string $pagination): void
    {
        $this->pagination = $pagination;
    }

    public function setRoot(?Chapter $root): void
    {
        $this->root = $root;
    }

    public function getRoot(): ?Chapter
    {
        return $this->root;
    }

    #[ORM\PostPersist]
    public function createRoot(PostPersistEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $rootLesson = $this->buildRoot();

        $em->getRepository(Chapter::class)->persistAsFirstChild($rootLesson);
        $em->flush();
    }

    public function buildRoot(): Chapter
    {
        $rootLesson = $this->getRoot();

        if (!$rootLesson) {
            $rootLesson = new Chapter();
            $rootLesson->setLesson($this);
            $rootLesson->setTitle('root_'.$this->getId());
            $this->setRoot($rootLesson);
        }

        return $rootLesson;
    }
}

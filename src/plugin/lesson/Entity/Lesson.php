<?php

namespace Icap\LessonBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'icap__lesson')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Lesson extends AbstractResource
{
    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    private ?string $description;

    /**
     * Show overview to users or directly start the lesson.
     */
    #[ORM\Column(name: 'show_overview', type: Types::BOOLEAN, options: ['default' => 1])]
    private bool $showOverview = true;

    
    #[ORM\JoinColumn(name: 'root_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: Chapter::class, cascade: ['all'])]
    private ?Chapter $root;

    /**
     * Numbering of the chapters.
     */
    #[ORM\Column]
    private string $numbering = 'none';

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description = null): void
    {
        $this->description = $description;
    }

    public function setShowOverview(bool $showOverview): void
    {
        $this->showOverview = $showOverview;
    }

    public function getShowOverview(): bool
    {
        return $this->showOverview;
    }

    public function setRoot(?Chapter $root): void
    {
        $this->root = $root;
    }

    public function getRoot(): ?Chapter
    {
        return $this->root;
    }

    public function getNumbering(): string
    {
        return $this->numbering;
    }

    public function setNumbering($numbering): void
    {
        $this->numbering = $numbering;
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

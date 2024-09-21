<?php

namespace Icap\LessonBundle\Entity;

use Icap\LessonBundle\Repository\ChapterRepository;
use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'icap__lesson_chapter')]
#[ORM\Entity(repositoryClass: ChapterRepository::class)]
#[Gedmo\Tree(type: 'nested')]
class Chapter
{
    use Id;
    use Uuid;
    use Poster;

    #[ORM\Column(type: Types::STRING, nullable: false)]
    private ?string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $internalNote;

    
    #[ORM\JoinColumn(name: 'lesson_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    private ?Lesson $lesson;

    #[ORM\Column(length: 128, unique: true, nullable: false)]
    #[Gedmo\Slug(fields: ['title'], unique: true, updatable: false)]
    private ?string $slug;

    #[ORM\Column(name: 'lft', type: Types::INTEGER)]
    #[Gedmo\TreeLeft]
    private ?int $left;

    #[ORM\Column(name: 'lvl', type: Types::INTEGER)]
    #[Gedmo\TreeLevel]
    private ?int $level;

    #[ORM\Column(name: 'rgt', type: Types::INTEGER)]
    #[Gedmo\TreeRight]
    private ?int $right;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Gedmo\TreeRoot]
    private ?int $root;

    
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Chapter::class)]
    #[Gedmo\TreeParent]
    private ?Chapter $parent = null;

    /**
     * Custom numbering of the chapters.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $customNumbering = '';

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setLeft(?int $left): void
    {
        $this->left = $left;
    }

    public function getLeft(): ?int
    {
        return $this->left;
    }

    public function setLesson(Lesson $lesson): void
    {
        $this->lesson = $lesson;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLevel(?int $level): void
    {
        $this->level = $level;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setParent(?Chapter $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?Chapter
    {
        return $this->parent;
    }

    public function setRight(?int $right): void
    {
        $this->right = $right;
    }

    public function getRight(): ?int
    {
        return $this->right;
    }

    public function setRoot(?int $root): void
    {
        $this->root = $root;
    }

    public function getRoot(): ?int
    {
        return $this->root;
    }

    public function setText(?string $text = null): void
    {
        $this->text = $text;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getInternalNote(): ?string
    {
        return $this->internalNote;
    }

    public function setInternalNote(string $internalNote = null): void
    {
        $this->internalNote = $internalNote;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getCustomNumbering(): string
    {
        return $this->customNumbering ?? '';
    }

    public function setCustomNumbering(?string $customNumbering): void
    {
        $this->customNumbering = $customNumbering;
    }
}

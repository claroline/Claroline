<?php

namespace Icap\LessonBundle\Entity;

use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="icap__lesson_chapter")
 * @ORM\Entity(repositoryClass="Icap\LessonBundle\Repository\ChapterRepository")
 * @Gedmo\Tree(type="nested")
 */
class Chapter
{
    use Id;
    use Uuid;
    use Poster;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $text;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $internalNote;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\LessonBundle\Entity\Lesson")
     *
     * @ORM\JoinColumn(name="lesson_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Lesson $lesson;

    /**
     * @Gedmo\Slug(fields={"title"}, unique=true, updatable=false)
     *
     * @ORM\Column(length=128, unique=true, nullable=false)
     */
    private ?string $slug;

    /**
     * @Gedmo\TreeLeft
     *
     * @ORM\Column(name="lft", type="integer")
     */
    private ?int $left;

    /**
     * @Gedmo\TreeLevel
     *
     * @ORM\Column(name="lvl", type="integer")
     */
    private ?int $level;

    /**
     * @Gedmo\TreeRight
     *
     * @ORM\Column(name="rgt", type="integer")
     */
    private ?int $right;

    /**
     * @Gedmo\TreeRoot
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $root;

    /**
     * @Gedmo\TreeParent
     *
     * @ORM\ManyToOne(targetEntity="Icap\LessonBundle\Entity\Chapter")
     *
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private ?Chapter $parent = null;

    /**
     * Custom numbering of the chapters.
     *
     * @ORM\Column(type="string", nullable=true)
     */
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

<?php

namespace Icap\LessonBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Poster;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="icap__lesson_chapter")
 * @ORM\Entity(repositoryClass="Icap\LessonBundle\Repository\ChapterRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Chapter
{
    use Id;
    use Uuid;
    use Poster;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $internalNote;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\LessonBundle\Entity\Lesson")
     * @ORM\JoinColumn(name="lesson_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $lesson;

    /**
     * @Gedmo\Slug(fields={"title"}, unique=true, updatable=false)
     * @ORM\Column(length=128, unique=true, nullable=false)
     */
    protected $slug;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $left;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $level;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $right;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Icap\LessonBundle\Entity\Chapter")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function setLeft($left)
    {
        $this->left = $left;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function setLesson(Lesson $lesson)
    {
        $this->lesson = $lesson;
    }

    public function getLesson(): Lesson
    {
        return $this->lesson;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setRight($right)
    {
        $this->right = $right;
    }

    public function getRight()
    {
        return $this->right;
    }

    public function setRoot($root)
    {
        $this->root = $root;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getInternalNote()
    {
        return $this->internalNote;
    }

    public function setInternalNote(string $internalNote = null)
    {
        $this->internalNote = $internalNote;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
}

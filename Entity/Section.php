<?php

namespace Icap\WikiBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="icap__wiki_section")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 */
class Section
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $visible=true;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @ORM\Column(type="datetime", name="created")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\WikiBundle\Entity\Wiki")
     * @ORM\JoinColumn(name="wiki_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $wiki;

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
     * @ORM\ManyToOne(targetEntity="Icap\WikiBundle\Entity\Section")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        return $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param mixed $visible
     */
    public function setVisible($visible)
    {
        return $this->visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        return $this->text = $text;
    }

    /**
     * Returns the resource creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set wiki
     *
     * @param \Icap\WikiBundle\Entity\Wiki $wiki
     * @return section
     */
    public function setWiki(\Icap\WikiBundle\Entity\Wiki $wiki)
    {
        $this->wiki = $wiki;

        return $this;
    }

    /**
     * Get wiki
     *
     * @return \Icap\WikiBundle\Entity\Wiki
     */
    public function getWiki()
    {
        return $this->wiki;
    }

    /**
     * @param mixed
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * @return mixed
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $right
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * @return mixed
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set parent
     *
     * @param \Icap\WikiBundle\Entity\Section $section
     * @return section
     */
    public function setParent(\Icap\WikiBundle\Entity\Section $section)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Icap\WikiBundle\Entity\Section
     */
    public function getParent()
    {
        return $this->parent;
    }    
}
<?php

namespace Claroline\DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="claro_directory")
 * use repository for handy tree functions
 */
class Directory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Claroline\DocumentBundle\Entity\Directory", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\DocumentBundle\Entity\Directory", mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\DocumentBundle\Entity\Document", mappedBy="directory")
     */
    private $documents;
    private $pathName;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setParent(Directory $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChildren(Directory $children)
    {
        $this->children[] = $children;
    }

    public function getPathName()
    {
        return $this->pathName;
    }

    public function setPathName($pathName)
    {
        $this->pathName = $pathName;
    }

    public function getDocuments()
    {
        return $this->documents;
    }

    public function addDocument(Document $document)
    {
        $this->documents[] = $document;
        $document->setDirectory($this);
    }

}

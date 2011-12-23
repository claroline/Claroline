<?php

namespace Claroline\CommonBundle\Tests\Stub\Entity\NodeHierarchy;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CommonBundle\Annotation\ORM as ORMExt;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="claro_test_tree_ancestor")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class TreeAncestor
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $treeAncestorField;

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
     * @ORM\ManyToOne(targetEntity="TreeAncestor", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="TreeAncestor", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;
    
    public function getId()
    {
        return $this->id;
    }

        
    public function getTreeAncestorField()
    {
        return $this->treeAncestorField;
    }

    public function setTreeAncestorField($value)
    {
        $this->treeAncestorField = $value;
    }
    
    public function setParent(TreeAncestor $parent)
    {
        $this->parent = $parent;
    }
     
    public function getParent()
    {
        return $this->parent;   
    }
}
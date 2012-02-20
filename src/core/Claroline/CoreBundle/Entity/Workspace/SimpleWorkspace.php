<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Exception\ClarolineException;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="claro_workspace")
 * @Gedmo\Tree(type="nested")
 */
class SimpleWorkspace extends AbstractWorkspace
{
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace", 
     *      inversedBy="children"
     * )
     * @ORM\JoinColumn(
     *      name="parent_id",
     *      referencedColumnName="id",
     *      onDelete="SET NULL"
     * )
     */
    private $parent;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace", 
     *      mappedBy="parent"
     * )
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;
    
    public function __construct()
    {
        parent::__construct();
        $this->children = new ArrayCollection();
    }
    
    public function setPublic($isPublic)
    {
        $parentWorkspace = $this->getParent();
        
        if (null !== $parentWorkspace)
        {
            if (true === $isPublic && ! $parentWorkspace->isPublic())
            {
                throw new ClarolineException(
                    'A sub-workspace of a private workspace cannot be made public'
                );
            }
        }
        
        $this->isPublic = $isPublic;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    
    public function setParent(SimpleWorkspace $workspace = null)
    {
        if ($this->isPublic() && ! $workspace->isPublic())
        {
            throw new ClarolineException(
                'A public workspace cannot be a sub-workspace of a private one'
            );
        }
        
        $this->parent = $workspace;
    }
}
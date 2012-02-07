<?php

namespace Claroline\CoreBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\CoreBundle\Exception\ClarolineException;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="claro_role")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "Claroline\CoreBundle\Entity\Role" = "Claroline\CoreBundle\Entity\Role",
 *      "Claroline\CoreBundle\Entity\WorkspaceRole" = "Claroline\CoreBundle\Entity\WorkspaceRole"
 * })
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Tree(type="nested")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Role implements RoleInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length="50")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(name="can_be_deleted", type="boolean")
     */
    private $canBeDeleted = true;
    
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
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Role", 
     *      inversedBy="children"
     * )
     * @ORM\JoinColumn(
     *      name="parent_id",
     *      referencedColumnName="id",
     *      onDelete="SET NULL"
     * )
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Role", 
     *      mappedBy="parent"
     * )
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;
    
    public function getId()
    {
        return $this->id;
    }

    final public function setName($name)
    {
        if (PlatformRoles::contains($this->name))
        {
            throw new ClarolineException('Platform roles cannot be modified');
        }
        
        if (PlatformRoles::contains($name))
        {
            $this->canBeDeleted = false;
        }
        
        $this->name = $name;
    }
        
    public function getName()
    {
        return $this->name;
    }
    
    public function canBeDeleted()
    {
        return $this->canBeDeleted;
    }

    /**
     * Alias of getName().
     * 
     * @return string The role name.
     */
    public function getRole()
    {
        return $this->getName();
    }
    
    public function setParent(Role $role = null)
    {
        $this->parent = $role;    
    }

    public function getParent()
    {
        return $this->parent;   
    }
    
    /** @ORM\PreRemove */
    public function preRemove()
    {
        if (PlatformRoles::contains($this->name))
        {
            throw new ClarolineException('Platform roles cannot be deleted');
        }
    }
}
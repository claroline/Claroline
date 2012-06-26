<?php

namespace Claroline\CoreBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
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
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length="50")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(name="translation_key", type="string", length="255")
     */
    private $translationKey;

    /**
     * @ORM\Column(name="is_read_only", type="boolean")
     */
    private $isReadOnly = false;

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
     *      targetEntity="Claroline\CoreBundle\Entity\Role",
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
     *      targetEntity="Claroline\CoreBundle\Entity\Role",
     *      mappedBy="parent"
     * )
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the role name. The name must be prefixed by 'ROLE_'. Note that
     * platform-wide roles (as listed in Claroline/CoreBundle/Security/PlatformRoles)
     * cannot be modified by this setter.
     *
     * @param string $name
     * @throw ClarolineException if the name isn't prefixed by 'ROLE_' or if the role is platform-wide
     */
    public function setName($name)
    {
        if (0 !== strpos($name, 'ROLE_')) {
            throw new ClarolineException('Role names must start with "ROLE_"');
        }

        if (PlatformRoles::contains($this->name)) {
            throw new ClarolineException('Platform roles cannot be modified');
        }

        if (PlatformRoles::contains($name)) {
            $this->isReadOnly = true;
        }

        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTranslationKey($key)
    {
        $this->translationKey = $key;
    }

    public function getTranslationKey()
    {
        if (null === $this->translationKey) {
            return $this->getName();
        }

        return $this->translationKey;
    }

    public function isReadOnly()
    {
        return $this->isReadOnly;
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

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        if (PlatformRoles::contains($this->name)) {
            throw new ClarolineException('Platform roles cannot be deleted');
        }
    }

    protected function setReadOnly($value)
    {
        $this->isReadOnly = $value;
    }
}
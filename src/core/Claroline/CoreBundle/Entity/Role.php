<?php

namespace Claroline\CoreBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_role")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "Claroline\CoreBundle\Entity\Role" = "Claroline\CoreBundle\Entity\Role",
 *      "Claroline\CoreBundle\Entity\WorkspaceRole" = "Claroline\CoreBundle\Entity\WorkspaceRole"
 * })
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

    
    public function getId()
    {
        return $this->id;
    }
        
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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
}
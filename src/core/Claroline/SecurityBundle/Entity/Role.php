<?php

namespace Claroline\SecurityBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\SecurityBundle\Repository\RoleRepository")
 * @ORM\Table(
 *     name="claro_role",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="role_idx", columns={"name"})}
 * )
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

    public function getRole()
    {
        return $this->getName();
    }
}
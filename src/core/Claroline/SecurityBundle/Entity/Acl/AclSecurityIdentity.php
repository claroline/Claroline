<?php

namespace Claroline\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

// Add the @ORM\Entity annotation to the class docblock to make this class an entity

/**
 * @ORM\Table(
 *      name="acl_security_identities", 
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unique_identifier_username", 
 *              columns={"identifier", "username"}
 *          )
 *      }
 * )
 */
class AclSecurityIdentity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="identifier", type="string", length=200, nullable=false)
     */
    private $identifier;

    /**
     * @ORM\Column(name="username", type="boolean", nullable=false)
     */
    private $username;
    
    public function getId()
    {
        return $this->id;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
<?php

namespace Claroline\CoreBundle\Entity\Acl;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
    protected $id;

    /**
     * @ORM\Column(name="identifier", type="string", length=200, nullable=false)
     */
    protected $identifier;

    /**
     * @ORM\Column(name="username", type="boolean", nullable=false)
     */
    protected $username;
}
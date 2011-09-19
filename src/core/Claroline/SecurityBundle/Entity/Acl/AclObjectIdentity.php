<?php

namespace Claroline\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

// Add the @ORM\Entity annotation to the class docblock to make this class an entity

/**
 * @ORM\Table(
 *      name="acl_object_identities",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unique_object_identifier_class_id", 
 *              columns={"object_identifier", "class_id"}
 *          )
 *      }
 * )
 */
class AclObjectIdentity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="class_id", type="integer", nullable=false)
     */
    private $classId;

    /**
     * @ORM\Column(name="object_identifier", type="string", length=100, nullable=false)
     */
    private $objectIdentifier;

    /**
     * @ORM\Column(name="entries_inheriting", type="boolean", nullable=false)
     */
    private $entriesInheriting;

    /**
     * @ORM\ManyToMany(targetEntity="AclObjectIdentity", inversedBy="objectIdentity")
     * @ORM\JoinTable(
     *      name="acl_object_identity_ancestors",
     *      joinColumns={
     *          @ORM\JoinColumn(name="object_identity_id", referencedColumnName="id", onUpdate="CASCADE", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="ancestor_id", referencedColumnName="id", onUpdate="CASCADE", onDelete="CASCADE")
     *      }
     * )
     */
    private $ancestor;

    /**
     * @ORM\ManyToOne(targetEntity="AclObjectIdentity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_object_identity_id", referencedColumnName="id")
     * })
     */
    private $parentObjectIdentity;

    public function __construct()
    {
        $this->ancestor = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getClassId()
    {
        return $this->classId;
    }

    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    public function getEntriesInheriting()
    {
        return $this->entriesInheriting;
    }

    public function getAncestor()
    {
        return $this->ancestor;
    }

    public function getParentObjectIdentity()
    {
        return $this->parentObjectIdentity;
    }
}
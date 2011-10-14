<?php

namespace Claroline\SecurityBundle\Entity\Acl;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
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
    protected $id;

    /**
     * @ORM\Column(name="class_id", type="integer", nullable=false)
     */
    protected $classId;

    /**
     * @ORM\Column(name="object_identifier", type="string", length=100, nullable=false)
     */
    protected $objectIdentifier;

    /**
     * @ORM\Column(name="entries_inheriting", type="boolean", nullable=false)
     */
    protected $entriesInheriting;

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
    protected $ancestor;

    /**
     * @ORM\ManyToOne(targetEntity="AclObjectIdentity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_object_identity_id", referencedColumnName="id")
     * })
     */
    protected $parentObjectIdentity;
}
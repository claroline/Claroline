<?php

namespace Claroline\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

// Add the @ORM\Entity annotation to the class docblock to make this class an entity

/**
 * @ORM\Table(
 *      name="acl_entries",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unique_class_id_object_identity_id_field_name_ace_order", 
 *              columns={"class_id", "object_identity_id", "field_name", "ace_order"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(
 *              name="index_class_id_object_identity_id_security_identity_id", 
 *              columns={"class_id", "object_identity_id", "security_identity_id"}
 *          )
 *      }
 * )
 */
class AclEntry
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="field_name", type="string", length=50, nullable=true)
     */
    private $fieldName;

    /**
     * @ORM\Column(name="ace_order", type="smallint", nullable=false)
     */
    private $aceOrder;

    /**
     * @ORM\Column(name="mask", type="integer", nullable=false)
     */
    private $mask;

    /**
     * @ORM\Column(name="granting", type="boolean", nullable=false)
     */
    private $granting;

    /**
     * @ORM\Column(name="granting_strategy", type="string", length=30, nullable=false)
     */
    private $grantingStrategy;

    /**
     * @ORM\Column(name="audit_success", type="boolean", nullable=false)
     */
    private $auditSuccess;

    /**
     * @ORM\Column(name="audit_failure", type="boolean", nullable=false)
     */
    private $auditFailure;

    /**
     * @ORM\ManyToOne(targetEntity="AclObjectIdentity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_identity_id", referencedColumnName="id", onUpdate="CASCADE", onDelete="CASCADE")
     * })
     */
    private $objectIdentity;

    /**
     * @ORM\ManyToOne(targetEntity="AclSecurityIdentity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="security_identity_id", referencedColumnName="id", nullable=false, onUpdate="CASCADE", onDelete="CASCADE")
     * })
     */
    private $securityIdentity;

    /**
     * @ORM\ManyToOne(targetEntity="AclClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="class_id", referencedColumnName="id", nullable=false, onUpdate="CASCADE", onDelete="CASCADE")
     * })
     */
    private $class;
}
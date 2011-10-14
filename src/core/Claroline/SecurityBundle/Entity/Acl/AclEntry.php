<?php

namespace Claroline\SecurityBundle\Entity\Acl;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
    protected $id;

    /**
     * @ORM\Column(name="field_name", type="string", length=50, nullable=true)
     */
    protected $fieldName;

    /**
     * @ORM\Column(name="ace_order", type="smallint", nullable=false)
     */
    protected $aceOrder;

    /**
     * @ORM\Column(name="mask", type="integer", nullable=false)
     */
    protected $mask;

    /**
     * @ORM\Column(name="granting", type="boolean", nullable=false)
     */
    protected $granting;

    /**
     * @ORM\Column(name="granting_strategy", type="string", length=30, nullable=false)
     */
    protected $grantingStrategy;

    /**
     * @ORM\Column(name="audit_success", type="boolean", nullable=false)
     */
    protected $auditSuccess;

    /**
     * @ORM\Column(name="audit_failure", type="boolean", nullable=false)
     */
    protected $auditFailure;

    /**
     * @ORM\ManyToOne(targetEntity="AclObjectIdentity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_identity_id", referencedColumnName="id", onUpdate="CASCADE", onDelete="CASCADE")
     * })
     */
    protected $objectIdentity;

    /**
     * @ORM\ManyToOne(targetEntity="AclSecurityIdentity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="security_identity_id", referencedColumnName="id", nullable=false, onUpdate="CASCADE", onDelete="CASCADE")
     * })
     */
    protected $securityIdentity;

    /**
     * @ORM\ManyToOne(targetEntity="AclClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="class_id", referencedColumnName="id", nullable=false, onUpdate="CASCADE", onDelete="CASCADE")
     * })
     */
    protected $class;
}
<?php

namespace Claroline\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

// Add the @ORM\Entity annotation to the class docblock to make this class an entity

/**
 * @ORM\Table(name="acl_classes")
 */
class AclClass
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="class_type", type="string", length=200, nullable=false, unique=true)
     */
    private $classType;
}
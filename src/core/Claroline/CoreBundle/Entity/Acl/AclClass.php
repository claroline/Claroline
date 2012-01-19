<?php

namespace Claroline\CoreBundle\Entity\Acl;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="acl_classes")
 */
class AclClass
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="class_type", type="string", length=200, nullable=false, unique=true)
     */
    protected $classType;
}
<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ProfilePropertyRepository")
 * @ORM\Table(name="claro_profile_property")
 */
class ProfileProperty
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_editable", type="boolean")
     */
    protected $isEditable;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="profileProperties",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $role;

    /**
     * @ORM\Column(name="property", length=256)
     */
    protected $property;

    public function getId()
    {
        return $this->id;
    }

    public function setIsEditable($bool)
    {
        $this->isEditable = $bool;
    }

    public function isEditable()
    {
        return $this->isEditable;
    }

    public function getIsEditable()
    {
        return $this->isEditable;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function getProperty()
    {
        return $this->property;
    }
}

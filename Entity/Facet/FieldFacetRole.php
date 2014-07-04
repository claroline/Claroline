<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Facet;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Role;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\FieldFacetRoleRepository")
 * @ORM\Table(name="claro_field_facet_role")
 */
class FieldFacetRole
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="fieldFacetsRole"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     inversedBy="fieldFacetsRole"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $fieldFacet;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $canOpen = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $canEdit = false;

    public function getId()
    {
        return $this->id;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setFieldFacet(FieldFacet $fieldFacet)
    {
        $this->fieldFacet = $fieldFacet;
    }

    public function getFieldFacet()
    {
        return $this->fieldFacet;
    }

    public function setCanOpen($bool)
    {
        $this->canOpen = $bool;
    }

    public function canOpen()
    {
        return $this->canOpen;
    }

    public function setCanEdit($bool)
    {
        $this->canEdit = $bool;
    }

    public function canEdit()
    {
        return $this->canEdit;
    }
}
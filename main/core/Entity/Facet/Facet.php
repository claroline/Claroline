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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Claroline\CoreBundle\Entity\Role;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\FacetRepository")
 * @ORM\Table(name="claro_facet")
 * @UniqueEntity("name")
 */
class Facet
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", name="position")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $position;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet",
     *     mappedBy="facet",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"api_facet_admin", "api_profile"})
     * @SerializedName("panels")
     */
    protected $panelFacets;

    /**
     * @var Role[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="facets"
     * )
     * @ORM\JoinTable(name="claro_facet_role")
     * @Groups({"api_facet_admin"})
     */
    protected $roles;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $forceCreationForm = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin", "api_profile"})
     */
    protected $isMain = false;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->panelFacets = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addPanelFacet(PanelFacet $panelFacet)
    {
        $this->panelFacets->add($panelFacet);
    }

    public function removePanelFacet(PanelFacet $panelFacet)
    {
        $this->panelFacets->removeElement($panelFacet);
    }

    public function getPanelFacets()
    {
        return $this->panelFacets;
    }

    public function resetPanelFacets()
    {
        $this->panelFacets = new ArrayCollection();
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function addRole(Role $role)
    {
        $this->roles->add($role);
    }

    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function setForceCreationForm($boolean)
    {
        $this->forceCreationForm = !is_bool($boolean) ? $boolean === 'true' : $boolean;
    }

    public function getForceCreationForm()
    {
        return $this->forceCreationForm;
    }

    public function setIsMain($boolean)
    {
        $this->isMain = !is_bool($boolean) ? $boolean === 'true' : $boolean;
    }

    public function isMain()
    {
        return $this->isMain;
    }
}

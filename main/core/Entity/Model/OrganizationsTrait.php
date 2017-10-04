<?php

namespace Claroline\CoreBundle\Entity\Model;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;

trait OrganizationsTrait
{
    /**
     * Add an organization.
     */
    public function addOrganization(Organization $organization)
    {
        $this->hasOrganizationsProperty();

        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }
    }

    /**
     * Removes an organization.
     */
    public function removeOrganization()
    {
        $this->hasOrganizationsProperty();

        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }
    }

    /**
     * Set the array directly.
     */
    public function setOrganizations($organizations)
    {
        $this->hasOrganizationsProperty();

        $this->organizations = $organizations instanceof ArrayCollection ?
            $organizations :
            new ArrayCollection($organizations);
    }

    /**
     * Get the organizations.
     */
    public function getOrganizations()
    {
        $this->hasOrganizationsProperty();

        return $this->organizations;
    }

    public function hasOrganizationsProperty()
    {
        if (!property_exists($this, 'organizations')) {
            $error = 'Property organizations does not exists in class '.get_class($this).'. This property is required if you want to patch it.';
            throw new \Exception($error);
        }
    }
}

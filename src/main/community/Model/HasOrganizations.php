<?php

namespace Claroline\CommunityBundle\Model;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait HasOrganizations
{
    /** @var Collection|Organization[] */
    private Collection $organizations;

    /**
     * Add an organization.
     */
    public function addOrganization(Organization $organization): void
    {
        if (!$this->organizations) {
            $this->organizations = new ArrayCollection();
        }

        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }
    }

    /**
     * Removes an organization.
     */
    public function removeOrganization($organization): void
    {
        if ($this->organizations && $this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }
    }

    /**
     * Set the array directly.
     */
    public function setOrganizations(array $organizations): void
    {
        if (!$this->organizations) {
            $this->organizations = new ArrayCollection();
        }

        $this->organizations->clear();
        foreach ($organizations as $organization) {
            $this->addOrganization($organization);
        }
    }

    /**
     * Get the organizations.
     *
     * @return Collection|Organization[]
     */
    public function getOrganizations(): Collection
    {
        return $this->organizations ?? new ArrayCollection();
    }
}

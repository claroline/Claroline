<?php

namespace Claroline\CommunityBundle\Model;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;

trait HasOrganizations
{
    /** @var ArrayCollection|Organization[] */
    private $organizations;

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
     * @return ArrayCollection|Organization[]
     */
    public function getOrganizations()
    {
        return $this->organizations ?? new ArrayCollection();
    }
}

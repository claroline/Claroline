<?php

namespace Claroline\CommunityBundle\Model;

use Claroline\CoreBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;

trait HasGroups
{
    /** @var ArrayCollection|Group[] */
    private $groups;

    /**
     * Add a group.
     */
    public function addGroup(Group $group): void
    {
        if (!$this->groups) {
            $this->groups = new ArrayCollection();
        }

        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }
    }

    public function hasGroup(Group $group): bool
    {
        if (!$this->groups) {
            return false;
        }

        return $this->groups->contains($group);
    }

    /**
     * Removes a group.
     */
    public function removeGroup(Group $group): void
    {
        if ($this->groups && $this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }
    }

    /**
     * Set the array directly.
     *
     * @param ArrayCollection|Group[] $groups
     */
    public function setGroups($groups): void
    {
        $this->groups = $groups instanceof ArrayCollection ?
            $groups :
            new ArrayCollection($groups);
    }

    /**
     * Get the groups.
     *
     * @return ArrayCollection|Group[]
     */
    public function getGroups()
    {
        return $this->groups ?? new ArrayCollection();
    }
}

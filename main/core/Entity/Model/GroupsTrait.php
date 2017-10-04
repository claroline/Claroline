<?php

namespace Claroline\CoreBundle\Entity\Model;

use Claroline\CoreBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;

trait GroupsTrait
{
    /**
     * Add an group.
     */
    public function addGroup(Group $group)
    {
        $this->hasGroupsProperty();

        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }
    }

    /**
     * Removes an group.
     */
    public function removeGroup(Group $group)
    {
        $this->hasGroupsProperty();

        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }
    }

    /**
     * Set the array directly.
     */
    public function setGroups($groups)
    {
        $this->hasGroupsProperty();

        $this->groups = $groups instanceof ArrayCollection ?
            $groups :
            new ArrayCollection($groups);
    }

    /**
     * Get the groups.
     */
    public function getGroups()
    {
        $this->hasGroupsProperty();

        return $this->groups;
    }

    public function hasGroupsProperty()
    {
        if (!property_exists($this, 'groups')) {
            $error = 'Property groups does not exists in class '.get_class($this).'. This property is required if you want to patch it.';
            throw new \Exception($error);
        }
    }
}

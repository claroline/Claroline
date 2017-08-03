<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\CompetencyBundle\Entity\Ability;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\AbilityProgressRepository")
 * @ORM\Table(name="hevinci_ability_progress")
 */
class AbilityProgress
{
    const STATUS_ACQUIRED = 'acquired';
    const STATUS_PENDING = 'pending';
    const STATUS_NOT_ATTEMPTED = 'not_attempted';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="HeVinci\CompetencyBundle\Entity\Ability")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $ability;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $user;

    /**
     * @ORM\Column(name="passed_resource_ids", type="simple_array", nullable=true)
     */
    private $passedResourceIds = [];

    /**
     * @ORM\Column(name="failed_resource_ids", type="simple_array", nullable=true)
     */
    private $failedResourceIds = [];

    /**
     * @ORM\Column(name="passed_resource_count", type="integer")
     */
    private $passedResourceCount = 0;

    /**
     * @ORM\Column
     */
    private $status = self::STATUS_NOT_ATTEMPTED;

    /**
     * @ORM\Column(name="ability_name", length=500)
     *
     * Note: this field retains the ability name in case it is deleted
     */
    private $abilityName;

    /**
     * @ORM\Column(name="user_name")
     *
     * Note: this field retains the user name in case it is deleted
     */
    private $userName;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Ability
     */
    public function getAbility()
    {
        return $this->ability;
    }

    /**
     * @param Ability $ability
     */
    public function setAbility(Ability $ability)
    {
        $this->ability = $ability;
        $this->abilityName = $ability->getName();
    }

    /**
     * @return int
     */
    public function getPassedResourceCount()
    {
        return $this->passedResourceCount;
    }

    /**
     * @return array
     */
    public function getPassedResourceIds()
    {
        return $this->passedResourceIds;
    }

    /**
     * @return array
     */
    public function getFailedResourceIds()
    {
        return $this->failedResourceIds;
    }

    /**
     * @param ResourceNode $resource
     *
     * @return bool
     */
    public function hasPassedResource(ResourceNode $resource)
    {
        return in_array($resource->getId(), $this->passedResourceIds);
    }

    /**
     * @param ResourceNode $resource
     */
    public function addPassedResource(ResourceNode $resource)
    {
        if (!$this->hasPassedResource($resource)) {
            $this->passedResourceIds[] = $resource->getId();
            ++$this->passedResourceCount;
            $this->removeFailedResource($resource);
        }
    }

    /**
     * @param ResourceNode $resource
     *
     * @return bool
     */
    public function hasFailedResource(ResourceNode $resource)
    {
        return !is_null($this->failedResourceIds) && in_array($resource->getId(), $this->failedResourceIds);
    }

    /**
     * @param ResourceNode $resource
     */
    public function addFailedResource(ResourceNode $resource)
    {
        if (!$this->hasPassedResource($resource) && !$this->hasFailedResource($resource)) {
            if (is_null($this->failedResourceIds)) {
                $this->failedResourceIds = [];
            }
            $this->failedResourceIds[] = $resource->getId();
        }
    }

    /**
     * @param ResourceNode $resource
     */
    public function removeFailedResource(ResourceNode $resource)
    {
        if (!is_null($this->failedResourceIds)) {
            $key = array_search($resource->getId(), $this->failedResourceIds);

            if ($key !== false) {
                array_splice($this->failedResourceIds, $key, 1);
            }
        }
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $this->userName = $user->getFirstName().' '.$user->getLastName();
    }

    /**
     * @return string
     */
    public function getAbilityName()
    {
        return $this->abilityName;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }
}

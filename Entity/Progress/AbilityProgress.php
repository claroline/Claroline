<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\CompetencyBundle\Entity\Ability;

/**
 * @ORM\Entity
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
     * @ORM\Column(name="passed_activity_ids", type="simple_array", nullable=true)
     */
    private $passedActivityIds = [];

    /**
     * @ORM\Column(name="passed_activity_count", type="integer")
     */
    private $passedActivityCount = 0;

    /**
     * @ORM\Column
     */
    private $status = self::STATUS_NOT_ATTEMPTED;

    /**
     * @ORM\Column(name="ability_name")
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
    public function getPassedActivityCount()
    {
        return $this->passedActivityCount;
    }

    /**
     * @return int
     */
    public function getPassedActivityIds()
    {
        return $this->passedActivityIds;
    }

    /**
     * @param Activity $activity
     * @return bool
     */
    public function hasPassedActivity(Activity $activity)
    {
        return in_array($activity->getId(), $this->passedActivityIds);
    }

    /**
     * @param Activity $activity
     */
    public function addPassedActivity(Activity $activity)
    {
        if (!$this->hasPassedActivity($activity)) {
            $this->passedActivityIds[] = $activity->getId();
            $this->passedActivityCount++;
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
        $this->userName = $user->getFirstName() . ' ' . $user->getLastName();
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

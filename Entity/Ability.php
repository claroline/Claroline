<?php

namespace HeVinci\CompetencyBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as BR;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\AbilityRepository")
 * @ORM\Table(name="hevinci_ability")
 * @BR\UniqueEntity("name")
 */
class Ability implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Range(min="0", max="1000")
     */
    private $minActivityCount = 1;

    /**
     * @ORM\OneToMany(targetEntity="CompetencyAbility", mappedBy="ability")
     */
    private $competencyAbilities;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\Activity")
     * @ORM\JoinTable(name="hevinci_ability_activity")
     */
    private $activities;

    /**
     * @ORM\Column(type="integer")
     *
     * Note: this field denormalizes $activities data
     *       in order to decrease query complexity.
     */
    private $activityCount = 0;

    /**
     * @var Level
     *
     * NOTE: this attribute is not mapped; its only purpose is to temporarily
     *       hold data for forms and json responses. Real level must be set
     *       on a associated CompetencyAbility instance.
     */
    private $level;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $count
     */
    public function setMinActivityCount($count)
    {
        $this->minActivityCount = $count;
    }

    /**
     * @return int
     */
    public function getMinActivityCount()
    {
        return $this->minActivityCount;
    }

    /**
     * @return CompetencyAbility[]
     */
    public function getCompetencyAbilities()
    {
        return $this->competencyAbilities;
    }

    /**
     * @see $level
     * @param Level $level
     */
    public function setLevel(Level $level)
    {
        $this->level = $level;
    }

    /**
     * @see $level
     * @return Level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param Activity $activity
     * @return bool
     */
    public function isLinkedToActivity(Activity $activity)
    {
        return $this->activities->contains($activity);
    }

    /**
     * Associates the ability with an activity.
     *
     * @param Activity $activity
     */
    public function linkActivity(Activity $activity)
    {
        if (!$this->isLinkedToActivity($activity)) {
            $this->activities->add($activity);
            $this->activityCount++;
        }
    }

    /**
     * Removes an association with an activity.
     *
     * @param Activity $activity
     */
    public function removeActivity(Activity $activity)
    {
        if (!$this->isLinkedToActivity($activity)) {
            $this->activities->removeElement($activity);
            $this->activityCount--;
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'activityCount' => $this->activityCount,
            'minActivityCount' => $this->minActivityCount,
            'levelName' => $this->level ? $this->level->getName() : null,
            'levelValue' => $this->level ? $this->level->getValue() : null
        ];
    }
}

<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\AbilityRepository")
 * @ORM\Table(name="hevinci_ability")
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
     * @ORM\Column
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
     * @var Level
     *
     * NOTE: this attribute is not mapped, its only purpose is to hold form data.
     */
    private $formLevel;

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
     * Unmapped attribute, only used in forms: level must be set
     * on a associated CompetencyAbility instance.
     *
     * @param Level $level
     */
    public function setLevel(Level $level)
    {
        $this->formLevel = $level;
    }

    /**
     * @see setLevel
     *
     * @return Level
     */
    public function getLevel()
    {
        return $this->formLevel;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'minActivityCount' => $this->minActivityCount
        ];
    }
}

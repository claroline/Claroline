<?php

namespace HeVinci\CompetencyBundle\Entity;

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
     * @var Level
     *
     * NOTE: this attribute is not mapped; its only purpose is to temporarily
     *       hold data for forms and json responses. Real level must be set
     *       on a associated CompetencyAbility instance.
     */
    private $level;

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

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'minActivityCount' => $this->minActivityCount,
            'levelName' => $this->level ? $this->level->getName() : null,
            'levelValue' => $this->level ? $this->level->getValue() : null
        ];
    }
}

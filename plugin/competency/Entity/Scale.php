<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\CompetencyBundle\Validator as CustomAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints as BR;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\ScaleRepository")
 * @ORM\Table(name="hevinci_scale")
 * @BR\UniqueEntity("name")
 */
class Scale implements \JsonSerializable
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
     * @ORM\OneToMany(
     *     targetEntity="Level",
     *     mappedBy="scale",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @CustomAssert\NotEmpty
     * @CustomAssert\NoDuplicate(property="name")
     */
    private $levels;

    /**
     * @ORM\OneToMany(targetEntity="Competency", mappedBy="scale")
     */
    private $competencies;

    /**
     * @var int
     *
     * Note: this attribute is not mapped; it is used only to hold
     *       data in HTTP responses
     */
    private $frameworkCount = 0;

    /**
     * @var int
     *
     * Note: this attribute is not mapped; it is used only to hold
     *       data in HTTP responses
     */
    private $abilityCount = 0;

    public function __construct()
    {
        $this->levels = new ArrayCollection();
    }

    /**
     * @return int
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
     * @param Level $level
     */
    public function addLevel(Level $level)
    {
        $this->levels->add($level);
    }

    /**
     * @return ArrayCollection
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @param ArrayCollection $levels
     */
    public function setLevels(ArrayCollection $levels)
    {
        $this->levels->clear();

        foreach ($levels as $level) {
            $level->setScale($this);
            $this->levels->add($level);
        }
    }

    /**
     * @see $frameworkCount
     *
     * @param int $count
     */
    public function setFrameworkCount($count)
    {
        $this->frameworkCount = $count;
    }

    /**
     * @see $frameworkCount
     *
     * @return int
     */
    public function getFrameworkCount()
    {
        return $this->frameworkCount;
    }

    /**
     * @see $abilityCount
     *
     * @param int $count
     */
    public function setAbilityCount($count)
    {
        $this->abilityCount = $count;
    }

    /**
     * @see $abilityCount
     *
     * @return int
     */
    public function getAbilityCount()
    {
        return $this->abilityCount;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'frameworkCount' => $this->frameworkCount,
            'abilityCount' => $this->abilityCount,
        ];
    }
}

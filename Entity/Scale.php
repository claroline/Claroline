<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\CompetencyBundle\Validator as CustomAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints as BR;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
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
     * @ORM\Column(name="is_locked", type="boolean")
     */
    private $isLocked = false;

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

    public function __construct()
    {
        $this->levels = new ArrayCollection();
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
     * @return bool
     */
    public function isLocked()
    {
        return $this->isLocked;
    }

    /**
     * @param bool $isLocked
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;
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

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isLocked' => $this->isLocked
        ];
    }
}

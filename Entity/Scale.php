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
class Scale
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
     *     cascade={"persist", "remove"}
     * )
     * @CustomAssert\NotEmpty
     * @CustomAssert\NoDuplicate(property="name")
     */
    private $levels;

    public function __construct()
    {
        $this->levels = new ArrayCollection();
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
        foreach ($levels as $level) {
            $level->setScale($this);
        }

        $this->levels = $levels;
    }
}

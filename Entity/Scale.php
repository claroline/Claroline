<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_scale")
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
     * @ORM\Column()
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Level", mappedBy="scale")
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
}

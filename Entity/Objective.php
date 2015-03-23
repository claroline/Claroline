<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HeVinci\CompetencyBundle\Validator as CustomAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints as BR;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_learning_objective")
 * @BR\UniqueEntity("name")
 */
class Objective implements \JsonSerializable
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

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}

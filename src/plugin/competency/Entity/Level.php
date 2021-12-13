<?php

namespace HeVinci\CompetencyBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\LevelRepository")
 * @ORM\Table(name="hevinci_level")
 */
class Level implements \JsonSerializable
{
    use Uuid;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Scale", inversedBy="levels")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $scale;

    /**
     * @ORM\OneToMany(targetEntity="CompetencyAbility", mappedBy="level")
     */
    private $competencyAbilities;

    public function __construct()
    {
        $this->refreshUuid();

        $this->competencyAbilities = new ArrayCollection();
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
     * @param int $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setScale(Scale $scale)
    {
        $this->scale = $scale;
    }

    /**
     * @return Scale
     */
    public function getScale()
    {
        return $this->scale;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}

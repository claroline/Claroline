<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\CompetencyBundle\Validator as CustomAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints as BR;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\ObjectiveRepository")
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
     * @ORM\OneToMany(targetEntity="ObjectiveCompetency", mappedBy="objective")
     */
    private $objectiveCompetencies;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="hevinci_objective_user")
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Group")
     * @ORM\JoinTable(name="hevinci_objective_group")
     */
    private $groups;

    public function __construct()
    {
        $this->objectiveCompetencies = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
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
     * @return ArrayCollection
     */
    public function getObjectiveCompetencies()
    {
        return $this->objectiveCompetencies;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'hasChildren' => $this->objectiveCompetencies->count() > 0
        ];
    }
}

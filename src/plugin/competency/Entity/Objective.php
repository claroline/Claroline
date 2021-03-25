<?php

namespace HeVinci\CompetencyBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as BR;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\ObjectiveRepository")
 * @ORM\Table(name="hevinci_learning_objective")
 * @BR\UniqueEntity("name")
 */
class Objective implements \JsonSerializable
{
    use Uuid;

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
        $this->refreshUuid();
        $this->objectiveCompetencies = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
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
     * @return ArrayCollection
     */
    public function getObjectiveCompetencies()
    {
        return $this->objectiveCompetencies;
    }

    public function addObjectiveCompetency(ObjectiveCompetency $link)
    {
        if (!$this->objectiveCompetencies->contains($link)) {
            $this->objectiveCompetencies->add($link);
            $link->setObjective($this);
        }
    }

    /**
     * @return bool
     */
    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }

    public function addUser(User $user)
    {
        if (!$this->hasUser($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(User $user)
    {
        if ($this->hasUser($user)) {
            $this->users->removeElement($user);
        }
    }

    /**
     * @return bool
     */
    public function hasGroup(Group $group)
    {
        return $this->groups->contains($group);
    }

    public function addGroup(Group $group)
    {
        if (!$this->hasGroup($group)) {
            $this->groups->add($group);
        }
    }

    public function removeGroup(Group $group)
    {
        if ($this->hasGroup($group)) {
            $this->groups->removeElement($group);
        }
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'hasChildren' => $this->objectiveCompetencies->count() > 0,
        ];
    }
}

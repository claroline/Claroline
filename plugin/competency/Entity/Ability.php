<?php

namespace HeVinci\CompetencyBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\Column(length=500, unique=true)
     * @Assert\NotBlank
     * @Assert\Length(max="500")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Range(min="0", max="1000")
     */
    private $minResourceCount = 1;

    /**
     * @ORM\OneToMany(targetEntity="CompetencyAbility", mappedBy="ability")
     */
    private $competencyAbilities;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinTable(name="hevinci_ability_resource")
     */
    private $resources;

    /**
     * @ORM\Column(type="integer")
     *
     * Note: this field denormalizes $resources data
     *       in order to decrease query complexity.
     */
    private $resourceCount = 0;

    /**
     * @var Level
     *
     * NOTE: this attribute is not mapped; its only purpose is to temporarily
     *       hold data for forms and json responses. Real level must be set
     *       on a associated CompetencyAbility instance
     */
    private $level;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
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
     * @param int $count
     */
    public function setMinResourceCount($count)
    {
        $this->minResourceCount = $count;
    }

    /**
     * @return int
     */
    public function getMinResourceCount()
    {
        return $this->minResourceCount;
    }

    /**
     * @return CompetencyAbility[]
     */
    public function getCompetencyAbilities()
    {
        return $this->competencyAbilities;
    }

    /**
     * @see $level
     *
     * @param Level $level
     */
    public function setLevel(Level $level)
    {
        $this->level = $level;
    }

    /**
     * @see $level
     *
     * @return Level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param ResourceNode $resource
     *
     * @return bool
     */
    public function isLinkedToResource(ResourceNode $resource)
    {
        return $this->resources->contains($resource);
    }

    /**
     * Associates the ability with a resource.
     *
     * @param ResourceNode $resource
     */
    public function linkResource(ResourceNode $resource)
    {
        if (!$this->isLinkedToResource($resource)) {
            $this->resources->add($resource);
            ++$this->resourceCount;
        }
    }

    /**
     * Removes an association with a resource.
     *
     * @param ResourceNode $resource
     */
    public function removeResource(ResourceNode $resource)
    {
        if ($this->isLinkedToResource($resource)) {
            $this->resources->removeElement($resource);
            --$this->resourceCount;
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getResources()
    {
        return $this->resources;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'resourceCount' => $this->resourceCount,
            'minResourceCount' => $this->minResourceCount,
            'levelName' => $this->level ? $this->level->getName() : null,
            'levelValue' => $this->level ? $this->level->getValue() : null,
        ];
    }
}

<?php

namespace Claroline\CoreBundle\Entity\Planning;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Repository\Planning\PlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_planning')]
#[ORM\Entity(repositoryClass: PlanningRepository::class)]
class Planning
{
    use Id;
    use Uuid;

    #[ORM\Column]
    private ?string $objectId = null;

    #[ORM\Column]
    private ?string $objectClass = null;

    /**
     * @var Collection<int, PlannedObject>
     */
    #[ORM\JoinTable(name: 'claro_planning_planned_object')]
    #[ORM\JoinColumn(name: 'planning_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'planned_object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: PlannedObject::class)]
    private Collection $plannedObjects;

    public function __construct()
    {
        $this->refreshUuid();

        $this->plannedObjects = new ArrayCollection();
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function setObjectClass(string $objectClass): void
    {
        $this->objectClass = $objectClass;
    }

    public function getPlannedObjects(): Collection
    {
        return $this->plannedObjects;
    }

    public function addPlannedObject(PlannedObject $plannedObject): void
    {
        if (!$this->plannedObjects->contains($plannedObject)) {
            $this->plannedObjects->add($plannedObject);
        }
    }

    public function removePlannedObject(PlannedObject $plannedObject): void
    {
        if ($this->plannedObjects->contains($plannedObject)) {
            $this->plannedObjects->removeElement($plannedObject);
        }
    }
}

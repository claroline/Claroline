<?php

namespace Claroline\CoreBundle\Entity\Planning;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_planning')]
#[ORM\Entity(repositoryClass: \Claroline\CoreBundle\Repository\Planning\PlanningRepository::class)]
class Planning
{
    use Id;
    use Uuid;

    /**
     * @var string
     */
    #[ORM\Column]
    private $objectId;

    /**
     * @var string
     */
    #[ORM\Column]
    private $objectClass;

    /**
     *
     * @var PlannedObject[]|ArrayCollection
     */
    #[ORM\JoinTable(name: 'claro_planning_planned_object')]
    #[ORM\JoinColumn(name: 'planning_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'planned_object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: \Claroline\CoreBundle\Entity\Planning\PlannedObject::class)]
    private $plannedObjects;

    public function __construct()
    {
        $this->refreshUuid();

        $this->plannedObjects = new ArrayCollection();
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function setObjectId(string $objectId)
    {
        $this->objectId = $objectId;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function setObjectClass(string $objectClass)
    {
        $this->objectClass = $objectClass;
    }

    public function getPlannedObjects()
    {
        return $this->plannedObjects;
    }

    public function addPlannedObject(PlannedObject $plannedObject)
    {
        if (!$this->plannedObjects->contains($plannedObject)) {
            $this->plannedObjects->add($plannedObject);
        }
    }

    public function removePlannedObject(PlannedObject $plannedObject)
    {
        if ($this->plannedObjects->contains($plannedObject)) {
            $this->plannedObjects->removeElement($plannedObject);
        }
    }
}

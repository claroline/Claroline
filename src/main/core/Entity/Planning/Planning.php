<?php

namespace Claroline\CoreBundle\Entity\Planning;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_planning")
 */
class Planning
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $objectId;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $objectClass;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Planning\PlannedObject")
     * @ORM\JoinTable(name="claro_planning_planned_object",
     *      joinColumns={@ORM\JoinColumn(name="planning_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="planned_object_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     *
     * @var PlannedObject[]|ArrayCollection
     */
    private $plannedObjects;

    public function __construct()
    {
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

<?php

namespace Claroline\CoreBundle\Entity\Planning;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * Define a planned object.
 *
 * Metadata are store in a linked PlannedObject to allow listing all PlannedObject at once (eg. DataSource, Agenda tool)
 * without having to grab data from different tables.
 * Although to keep implementations simple and API structure clean, this complexity is hidden :
 *   - Methods of PlannedObject are wrapped in this class to avoid using AbstractPlanned::getPlannedObject()->get*()/AbstractPlanned::getPlannedObject()->set*().
 *   - When serializing a AbstractPlanned object, the custom props of the object and the props of the PlannedObject SHOULD be merged.
 * Implementations SHOULD NOT directly manipulate the PlannedObject.
 *
 * @ORM\MappedSuperclass()
 */
abstract class AbstractPlanned
{
    use Id;
    use Uuid;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Planning\PlannedObject", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="planned_object_id", onDelete="CASCADE", nullable=false)
     *
     * @var PlannedObject
     */
    protected $plannedObject;

    public function __construct()
    {
        $this->refreshUuid();
    }

    abstract public static function getType(): string;

    public function setUuid(string $uuid)
    {
        // keep uuids synced to have the same id when we serialize the generic planned object
        // and when we serialize an implementation
        $this->getPlannedObject()->setUuid($uuid);

        // we must keep the uuid locally to make the standard crud api work
        $this->uuid = $uuid;
    }

    /**
     * Auto wrap getters/setters of the PlannedObject to hide the relation and keep implementations simple.
     * NB. It would have been better to avoid magic call, but there are too many methods to keep synced.
     */
    public function __call(string $method, array $arguments = [])
    {
        if (method_exists($this->plannedObject, $method)) {
            return call_user_func_array([$this->plannedObject, $method], $arguments);
        }

        throw new \BadMethodCallException(sprintf('Undefined method "%s".', $method));
    }

    public function getPlannedObject(): PlannedObject
    {
        if (empty($this->plannedObject)) {
            $this->plannedObject = new PlannedObject();
            $this->plannedObject->setType(static::getType());
            $this->plannedObject->setClass(static::class);
            $this->plannedObject->setUuid($this->uuid);
        }

        return $this->plannedObject;
    }

    public function setPlannedObject(PlannedObject $plannedObject)
    {
        $this->plannedObject = $plannedObject;
    }
}

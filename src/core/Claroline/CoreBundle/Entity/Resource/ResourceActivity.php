<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceActivityRepository")
 * @ORM\Table(
 *     name="claro_resource_activity",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="resource_activity_unique_combination",
 *             columns={"activity_id", "resource_id"}
 *         )
 *     }
 * )
 */
class ResourceActivity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\Activity",
     *     inversedBy="resourcesActivities"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $activity;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $resource;

    /**
     * @ORM\Column(name="sequence_order", type="integer", nullable=true)
     */
    protected $sequenceOrder;

    public function setActivity(Activity $activity)
    {
        $this->activity = $activity;
        $activity->addResourceActivity($this);
    }

    public function getActivity()
    {
        return $this->activity;
    }

    public function setResource(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setSequenceOrder($order)
    {
        $this->sequenceOrder = $order;
    }

    public function getSequenceOrder()
    {
        return $this->sequenceOrder;
    }

    public function getId()
    {
        return $this->id;
    }
}

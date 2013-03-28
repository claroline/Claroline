<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceActivityRepository")
 * @ORM\Table(name="claro_resource_activity")
 */
class ResourceActivity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\Activity",
     *     inversedBy="resourcesActivities"
     * )
     */
    private $activity;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource")
     */
    private $resource;

    /**
     * @ORM\Column(type="string", nullable=true, name="sequence_order")
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
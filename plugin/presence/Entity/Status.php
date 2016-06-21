<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Status.
 *
 * @ORM\Table(name="formalibre_presencebundle_status")
 * @ORM\Entity
 */
class Status
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="statusName", type="string", length=255, nullable=true)
     */
    private $statusName;

    /**
     * @var string
     *
     * @ORM\Column(name="statusColor", type="string", length=255, nullable=true)
     */
    private $statusColor;

    /**
     * @var string
     *
     * @ORM\Column(name="statusByDefault", type="boolean" )
     */
    private $statusByDefault = false;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set statusName.
     *
     * @param string $statusName
     *
     * @return Status
     */
    public function setStatusName($statusName)
    {
        $this->statusName = $statusName;

        return $this;
    }

    /**
     * Get statusName.
     *
     * @return string
     */
    public function getStatusName()
    {
        return $this->statusName;
    }

    /**
     * Set statusColor.
     *
     * @param string $statusColor
     *
     * @return Status
     */
    public function setStatusColor($statusColor)
    {
        $this->statusColor = $statusColor;

        return $this;
    }

    /**
     * Get statusColor.
     *
     * @return string
     */
    public function getStatusColor()
    {
        return $this->statusColor;
    }

    public function getStatusByDefault()
    {
        return $this->statusByDefault;
    }

    public function setStatusByDefault($statusByDefault)
    {
        $this->statusByDefault = $statusByDefault;
    }
}

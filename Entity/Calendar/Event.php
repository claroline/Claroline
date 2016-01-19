<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Calendar;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro__event")
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Groups({"api"})    
     */
    protected $type;

    /**
     * @ORM\Column()
     * @Groups({"api"})    
     */
    protected $name;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"},
     *     inversedBy="events"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE", nullable=true)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     cascade={"persist"},
     *     inversedBy="events"
     * )
     * @ORM\JoinColumn(name="workspace_id", onDelete="CASCADE", nullable=true)
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Calendar\TimeSlot",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="time_slot_id", onDelete="CASCADE", nullable=false)
     */
    protected $timeSlot;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    public function getId()
    {
        return $this->id;
    }

    public function setDetails(array $details)
    {
        $this->details = $details;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setTimeSlot(TimeSlot $timeSlot)
    {
        $this->timeSlot = $timeSlot;
    }

    public function getTimeSlot()
    {
        return $this->timeSlot;
    }
}

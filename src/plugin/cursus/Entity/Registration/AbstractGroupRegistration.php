<?php

namespace Claroline\CursusBundle\Entity\Registration;

use Claroline\CoreBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractGroupRegistration extends AbstractRegistration
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", nullable=false, onDelete="CASCADE")
     *
     * @var Group
     */
    protected $group;

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group)
    {
        $this->group = $group;
    }
}

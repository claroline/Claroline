<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_cursusbundle_course_session_group",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="cursus_group_unique_course_session_group",
 *             columns={"session_id", "group_id", "group_type"}
 *         )
 *     }
 * )
 */
class CourseSessionGroup
{
    use Id;
    use Uuid;

    const TYPE_LEARNER = 0;
    const TYPE_TEACHER = 1;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Group"
     * )
     * @ORM\JoinColumn(name="group_id", nullable=false, onDelete="CASCADE")
     *
     * @var Group
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     inversedBy="sessionGroups"
     * )
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     *
     * @var CourseSession
     */
    protected $session;

    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $registrationDate;

    /**
     * @ORM\Column(name="group_type", type="integer", nullable=false)
     */
    protected $groupType = self::TYPE_LEARNER;

    public function __construct()
    {
        $this->refreshUuid();
        $this->registrationDate = new \DateTime();
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function setSession(CourseSession $session)
    {
        $this->session = $session;
    }

    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    }

    public function getGroupType()
    {
        return $this->groupType;
    }

    public function setGroupType($groupType)
    {
        $this->groupType = $groupType;
    }
}

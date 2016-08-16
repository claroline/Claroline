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

use Claroline\CoreBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseSessionGroupRepository")
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
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus", "api_cursus_min", "api_group_min"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Group"
     * )
     * @ORM\JoinColumn(name="group_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_group_min"})
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     inversedBy="sessionGroups"
     * )
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_cursus", "api_group_min"})
     */
    protected $session;

    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=false)
     * @Groups({"api_cursus", "api_cursus_min", "api_group_min"})
     * @SerializedName("registrationDate")
     */
    protected $registrationDate;

    /**
     * @ORM\Column(name="group_type", type="integer", nullable=false)
     * @Groups({"api_cursus", "api_cursus_min", "api_group_min"})
     * @SerializedName("groupType")
     */
    protected $groupType = 0;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

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

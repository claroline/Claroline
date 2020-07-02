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

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_cursusbundle_course")
 * @DoctrineAssert\UniqueEntity("code")
 */
class Course extends AbstractCourseSession
{
    use UuidTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_model_id", nullable=true, onDelete="SET NULL")
     */
    protected $workspaceModel;

    /**
     * @ORM\Column(name="tutor_role_name", nullable=true)
     */
    protected $tutorRoleName;

    /**
     * @ORM\Column(name="learner_role_name", nullable=true)
     */
    protected $learnerRoleName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     mappedBy="course"
     * )
     */
    protected $sessions;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $icon;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="claro_cursusbundle_course_validators")
     */
    protected $validators;

    /**
     * @ORM\Column(name="session_duration", nullable=false, type="integer", options={"default" = 1})
     */
    protected $defaultSessionDuration = 1;

    /**
     * @ORM\Column(name="with_session_event", type="boolean", options={"default" = 1})
     */
    private $withSessionEvent = true;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization"
     * )
     * @ORM\JoinTable(name="claro_cursusbundle_course_organizations")
     */
    protected $organizations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\Cursus",
     *     mappedBy="course"
     * )
     */
    protected $cursus;

    public function __construct()
    {
        $this->refreshUuid();
        $this->sessions = new ArrayCollection();
        $this->validators = new ArrayCollection();
        $this->organizations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getWorkspaceModel()
    {
        return $this->workspaceModel;
    }

    public function setWorkspaceModel(Workspace $workspace = null)
    {
        $this->workspaceModel = $workspace;
    }

    public function getTutorRoleName()
    {
        return $this->tutorRoleName;
    }

    public function setTutorRoleName($tutorRoleName)
    {
        $this->tutorRoleName = $tutorRoleName;
    }

    public function getLearnerRoleName()
    {
        return $this->learnerRoleName;
    }

    public function setLearnerRoleName($learnerRoleName)
    {
        $this->learnerRoleName = $learnerRoleName;
    }

    public function getSessions()
    {
        return $this->sessions;
    }

    public function getDefaultSession()
    {
        $defaultSession = null;

        foreach ($this->sessions as $session) {
            if ($session->isDefaultSession()) {
                $defaultSession = $session;
                break;
            }
        }

        return $defaultSession;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getValidators()
    {
        return $this->validators->toArray();
    }

    public function addValidator(User $validator)
    {
        if (!$this->validators->contains($validator)) {
            $this->validators->add($validator);
        }

        return $this;
    }

    public function removeValidator(User $validator)
    {
        if ($this->validators->contains($validator)) {
            $this->validators->removeElement($validator);
        }

        return $this;
    }

    public function emptyValidators()
    {
        $this->validators->clear();
    }

    public function hasValidation()
    {
        return parent::hasValidation() || 0 < count($this->getValidators());
    }

    public function getDefaultSessionDuration()
    {
        return $this->defaultSessionDuration;
    }

    public function setDefaultSessionDuration($defaultSessionDuration)
    {
        $this->defaultSessionDuration = $defaultSessionDuration;
    }

    public function getWithSessionEvent()
    {
        return $this->withSessionEvent;
    }

    public function setWithSessionEvent($withSessionEvent)
    {
        $this->withSessionEvent = $withSessionEvent;
    }

    public function getOrganizations()
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization)
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization)
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }

        return $this;
    }

    public function emptyOrganizations()
    {
        $this->organizations->clear();
    }

    public function __toString()
    {
        return $this->getTitle().' ['.$this->getCode().']';
    }
}

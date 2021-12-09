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

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseRepository")
 * @ORM\Table(name="claro_cursusbundle_course")
 * @DoctrineAssert\UniqueEntity("code")
 */
class Course extends AbstractTraining
{
    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     *
     * @var string
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Course", inversedBy="children")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     *
     * @var Course
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CursusBundle\Entity\Course", mappedBy="parent")
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @var Course[]|ArrayCollection
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_model_id", nullable=true, onDelete="SET NULL")
     */
    private $workspaceModel;

    /**
     * @ORM\Column(name="tutor_role_name", nullable=true)
     */
    private $tutorRoleName;

    /**
     * @ORM\Column(name="learner_role_name", nullable=true)
     */
    private $learnerRoleName;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CursusBundle\Entity\Session", mappedBy="course")
     *
     * @var Session[]
     */
    private $sessions;

    /**
     * Hides sessions to users.
     *
     * @ORM\Column(type="boolean")
     *
     * @var string
     */
    private $hideSessions = false;

    /**
     * If true, automatically register users to the default session of the training children
     * when registering to a session of this training.
     *
     * @ORM\Column(type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    private $propagateRegistration = false;

    /**
     * Configure which session to open when opening the course.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $sessionOpening = 'first_available';

    /**
     * @ORM\Column(name="session_duration", nullable=false, type="float", options={"default" = 1})
     */
    private $defaultSessionDuration = 1;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization"
     * )
     * @ORM\JoinTable(name="claro_cursusbundle_course_organizations")
     */
    private $organizations;

    /**
     * Course constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->sessions = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug = null)
    {
        $this->slug = $slug;
    }

    public function getWorkspaceModel(): ?Workspace
    {
        return $this->workspaceModel;
    }

    public function setWorkspaceModel(?Workspace $workspace = null)
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

    public function getPropagateRegistration(): bool
    {
        return $this->propagateRegistration;
    }

    public function setPropagateRegistration(bool $propagate)
    {
        $this->propagateRegistration = $propagate;
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

    public function hasAvailableSession()
    {
        $now = new \DateTime();
        foreach ($this->sessions as $session) {
            if (empty($session->getEndDate()) || $session->getEndDate() > $now) {
                return true;
            }
        }

        return false;
    }

    public function getHideSessions(): bool
    {
        return $this->hideSessions;
    }

    public function setHideSessions(bool $hideSessions)
    {
        $this->hideSessions = $hideSessions;
    }

    public function getSessionOpening(): ?string
    {
        return $this->sessionOpening;
    }

    public function setSessionOpening(string $sessionOpening)
    {
        $this->sessionOpening = $sessionOpening;
    }

    public function getDefaultSessionDuration()
    {
        return $this->defaultSessionDuration;
    }

    public function setDefaultSessionDuration($defaultSessionDuration)
    {
        $this->defaultSessionDuration = $defaultSessionDuration;
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

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Course $parent = null)
    {
        $this->parent = $parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChild(Course $course)
    {
        if (!$this->children->contains($course)) {
            $this->children->add($course);
        }
    }

    public function removeChild(Course $course)
    {
        if ($this->children->contains($course)) {
            $this->children->removeElement($course);
        }
    }

    public function __toString()
    {
        return $this->getName().' ['.$this->getCode().']';
    }
}

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

use Claroline\CommunityBundle\Model\HasOrganizations;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    use HasOrganizations;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private string $slug;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Course", inversedBy="children")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private ?Course $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CursusBundle\Entity\Course", mappedBy="parent")
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @var Collection|Course[]
     */
    private Collection $children;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_model_id", nullable=true, onDelete="SET NULL")
     */
    private ?Workspace $workspaceModel;

    /**
     * @ORM\Column(name="tutor_role_name", nullable=true)
     */
    private ?string $tutorRoleName = null;

    /**
     * @ORM\Column(name="learner_role_name", nullable=true)
     */
    private ?string $learnerRoleName = null;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CursusBundle\Entity\Session", mappedBy="course")
     *
     * @var Collection|Session[]
     */
    private Collection $sessions;

    /**
     * Hides sessions to users.
     *
     * @ORM\Column(type="boolean")
     */
    private bool $hideSessions = false;

    /**
     * Configure which session to open when opening the course.
     *
     * @ORM\Column(nullable=true)
     */
    private ?string $sessionOpening = 'first_available';

    /**
     * @ORM\Column(name="session_duration", nullable=false, type="float", options={"default" = 1})
     */
    private float $defaultSessionDuration = 1;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization"
     * )
     * @ORM\JoinTable(name="claro_cursusbundle_course_organizations")
     */
    private Collection $organizations;

    /**
     * A list of custom panels and fields for the user registration form.
     *
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacet", cascade={"persist"})
     * @ORM\JoinTable(name="claro_cursusbundle_course_panel_facet",
     *     joinColumns={@ORM\JoinColumn(name="course_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="panel_facet_id", referencedColumnName="id", onDelete="CASCADE", unique=true)}
     * )
     *
     * @var Collection|PanelFacet[]
     */
    private Collection $panelFacets;

    public function __construct()
    {
        $this->refreshUuid();

        $this->sessions = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->panelFacets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName().' ['.$this->getCode().']';
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getWorkspaceModel(): ?Workspace
    {
        return $this->workspaceModel;
    }

    public function setWorkspaceModel(?Workspace $workspace = null): void
    {
        $this->workspaceModel = $workspace;
    }

    public function getTutorRoleName(): ?string
    {
        return $this->tutorRoleName;
    }

    public function setTutorRoleName(?string $tutorRoleName = null): void
    {
        $this->tutorRoleName = $tutorRoleName;
    }

    public function getLearnerRoleName(): ?string
    {
        return $this->learnerRoleName;
    }

    public function setLearnerRoleName(?string $learnerRoleName = null): void
    {
        $this->learnerRoleName = $learnerRoleName;
    }

    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function getDefaultSession(): ?Session
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

    public function hasAvailableSession(): bool
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

    public function setHideSessions(bool $hideSessions): void
    {
        $this->hideSessions = $hideSessions;
    }

    public function getSessionOpening(): ?string
    {
        return $this->sessionOpening;
    }

    public function setSessionOpening(string $sessionOpening): void
    {
        $this->sessionOpening = $sessionOpening;
    }

    public function getDefaultSessionDuration(): float
    {
        return $this->defaultSessionDuration;
    }

    public function setDefaultSessionDuration($defaultSessionDuration): void
    {
        $this->defaultSessionDuration = $defaultSessionDuration;
    }

    public function getParent(): ?Course
    {
        return $this->parent;
    }

    public function setParent(?Course $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Course $course): void
    {
        if (!$this->children->contains($course)) {
            $this->children->add($course);
        }
    }

    public function removeChild(Course $course): void
    {
        if ($this->children->contains($course)) {
            $this->children->removeElement($course);
        }
    }

    public function getPanelFacets(): Collection
    {
        return $this->panelFacets;
    }

    public function addPanelFacet(PanelFacet $panelFacet): void
    {
        if (!$this->panelFacets->contains($panelFacet)) {
            $this->panelFacets->add($panelFacet);
        }
    }

    public function removePanelFacet(PanelFacet $panelFacet): void
    {
        if ($this->panelFacets->contains($panelFacet)) {
            $this->panelFacets->removeElement($panelFacet);
        }
    }
}

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

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Meta\Archived;
use Claroline\AppBundle\Entity\Meta\IsPublic;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CommunityBundle\Model\HasOrganizations;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CursusBundle\Finder\CourseType;
use Claroline\CursusBundle\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'claro_cursusbundle_course')]
#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[CrudEntity(finderClass: CourseType::class)]
class Course extends AbstractTraining
{
    use Name;
    use Order;
    use Poster;
    use HasOrganizations;
    use IsPublic;
    use Archived;

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug;

    #[ORM\Column(nullable: true)]
    private ?string $plainDescription = null;

    /**
     * If the course grants a certification at the end, explains the certificates the user can obtain.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $certification = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectives = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $requirements = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $targetAudience = null;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'course')]
    private Collection $sessions;

    /**
     * Hides sessions to users.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hideSessions = false;

    /**
     * Configure which session to open when opening the course.
     */
    #[ORM\Column(nullable: true)]
    private ?string $sessionOpening = 'first_available';

    #[ORM\Column(name: 'session_duration', type: Types::FLOAT, nullable: false, options: ['default' => 1])]
    private float $defaultSessionDuration = 1; // in hours

    /**
     * @var Collection<int, Organization>
     */
    #[ORM\JoinTable(name: 'claro_cursusbundle_course_organizations')]
    #[ORM\ManyToMany(targetEntity: Organization::class)]
    private Collection $organizations;

    /**
     * A list of custom panels and fields for the user registration form.
     *
     * @var Collection<int, PanelFacet>
     */
    #[ORM\ManyToMany(targetEntity: PanelFacet::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'claro_cursusbundle_course_panel_facet')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'panel_facet_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    private Collection $panelFacets;

    public function __construct()
    {
        $this->refreshUuid();

        $this->sessions = new ArrayCollection();
        $this->organizations = new ArrayCollection();
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

    public function getPlainDescription(): ?string
    {
        return $this->plainDescription;
    }

    public function setPlainDescription(?string $description = null): void
    {
        $this->plainDescription = $description;
    }

    public function getCertification(): ?string
    {
        return $this->certification;
    }

    public function setCertification(?string $certification): void
    {
        $this->certification = $certification;
    }

    public function getObjectives(): ?string
    {
        return $this->objectives;
    }

    public function setObjectives(?string $objectives = null): void
    {
        $this->objectives = $objectives;
    }

    public function getRequirements(): ?string
    {
        return $this->requirements;
    }

    public function setRequirements(?string $requirements = null): void
    {
        $this->requirements = $requirements;
    }

    public function getTargetAudience(): ?string
    {
        return $this->targetAudience;
    }

    public function setTargetAudience(?string $targetAudience = null): void
    {
        $this->targetAudience = $targetAudience;
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

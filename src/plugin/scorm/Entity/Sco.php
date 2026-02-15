<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_scorm_sco')]
#[ORM\Entity]
class Sco
{
    use Id;
    use Uuid;

    #[ORM\ManyToOne(targetEntity: Scorm::class, cascade: ['persist'], inversedBy: 'scos')]
    #[ORM\JoinColumn(name: 'scorm_id', nullable: false, onDelete: 'CASCADE')]
    private ?Scorm $scorm = null;

    #[ORM\ManyToOne(targetEntity: Sco::class, inversedBy: 'scoChildren')]
    #[ORM\JoinColumn(name: 'sco_parent_id', nullable: true, onDelete: 'CASCADE')]
    private ?Sco $scoParent = null;

    /**
     * @var Collection<int, Sco>
     */
    #[ORM\OneToMany(targetEntity: Sco::class, mappedBy: 'scoParent')]
    private Collection $scoChildren;

    #[ORM\Column(name: 'entry_url', nullable: true)]
    private ?string $entryUrl = null;

    #[ORM\Column(nullable: false)]
    private ?string $identifier = null;

    #[ORM\Column(nullable: false)]
    private ?string $title = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private ?bool $visible = true;

    #[ORM\Column(name: 'sco_parameters', type: Types::TEXT, nullable: true)]
    private ?string $parameters = null;

    #[ORM\Column(name: 'launch_data', type: Types::TEXT, nullable: true)]
    private ?string $launchData = null;

    #[ORM\Column(name: 'max_time_allowed', nullable: true)]
    private ?string $maxTimeAllowed = null;

    #[ORM\Column(name: 'time_limit_action', nullable: true)]
    private ?string $timeLimitAction = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private ?bool $block = false;

    /**
     * Score to pass for Scorm 1.2.
     */
    #[ORM\Column(name: 'score_int', type: Types::INTEGER, nullable: true)]
    private ?int $scoreToPassInt = null;

    /**
     * Score to pass for Scorm 2004.
     */
    #[ORM\Column(name: 'score_decimal', type: Types::FLOAT, precision: 10, scale: 7, nullable: true)]
    private ?float $scoreToPassDecimal = null;

    /**
     * For Scorm 2004 only.
     */
    #[ORM\Column(name: 'completion_threshold', type: Types::FLOAT, precision: 10, scale: 7, nullable: true)]
    private ?float $completionThreshold = null;

    /**
     * For Scorm 1.2 only.
     */
    #[ORM\Column(nullable: true)]
    private ?string $prerequisites = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->scoChildren = new ArrayCollection();
    }

    public function getScorm(): ?Scorm
    {
        return $this->scorm;
    }

    public function setScorm(?Scorm $scorm = null): void
    {
        $this->scorm = $scorm;
    }

    public function getScoParent(): ?Sco
    {
        return $this->scoParent;
    }

    public function setScoParent(?Sco $scoParent = null): void
    {
        $this->scoParent = $scoParent;
    }

    public function getScoChildren(): Collection
    {
        return $this->scoChildren;
    }

    /**
     * @param Collection<int, Sco> $scoChildren
     */
    public function setScoChildren(Collection $scoChildren): void
    {
        $this->scoChildren = $scoChildren;
    }

    public function getEntryUrl(): ?string
    {
        return $this->entryUrl;
    }

    public function setEntryUrl(string $entryUrl): void
    {
        $this->entryUrl = $entryUrl;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getParameters(): ?string
    {
        return $this->parameters;
    }

    public function setParameters(?string $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getLaunchData(): ?string
    {
        return $this->launchData;
    }

    public function setLaunchData(?string $launchData): void
    {
        $this->launchData = $launchData;
    }

    public function getMaxTimeAllowed(): ?string
    {
        return $this->maxTimeAllowed;
    }

    public function setMaxTimeAllowed(?string $maxTimeAllowed): void
    {
        $this->maxTimeAllowed = $maxTimeAllowed;
    }

    public function getTimeLimitAction(): ?string
    {
        return $this->timeLimitAction;
    }

    public function setTimeLimitAction(?string $timeLimitAction): void
    {
        $this->timeLimitAction = $timeLimitAction;
    }

    public function isBlock(): bool
    {
        return $this->block;
    }

    public function setBlock(bool $block): void
    {
        $this->block = $block;
    }

    public function getScoreToPass(): float|int|null
    {
        if (Scorm::SCORM_2004 === $this->scorm->getVersion()) {
            return $this->scoreToPassDecimal;
        }

        return $this->scoreToPassInt;
    }

    public function setScoreToPass(float|int|null $scoreToPass): void
    {
        if (Scorm::SCORM_2004 === $this->scorm->getVersion()) {
            $this->setScoreToPassDecimal($scoreToPass);
        } else {
            $this->setScoreToPassInt($scoreToPass);
        }
    }

    public function getScoreToPassInt(): ?int
    {
        return $this->scoreToPassInt;
    }

    public function setScoreToPassInt(?int $scoreToPassInt): void
    {
        $this->scoreToPassInt = $scoreToPassInt;
    }

    public function getScoreToPassDecimal(): ?float
    {
        return $this->scoreToPassDecimal;
    }

    public function setScoreToPassDecimal(?float $scoreToPassDecimal): void
    {
        $this->scoreToPassDecimal = $scoreToPassDecimal;
    }

    public function getCompletionThreshold(): ?float
    {
        return $this->completionThreshold;
    }

    public function setCompletionThreshold(?float $completionThreshold): void
    {
        $this->completionThreshold = $completionThreshold;
    }

    public function getPrerequisites(): ?string
    {
        return $this->prerequisites;
    }

    public function setPrerequisites(?string $prerequisites): void
    {
        $this->prerequisites = $prerequisites;
    }
}

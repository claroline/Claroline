<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Color;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Archived;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\CoreBundle\Entity\Model\Template;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Finder\BadgeType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents an obtainable badge.
 */
#[ORM\Table(name: 'claro__open_badge_badge_class')]
#[ORM\Entity]
#[CrudEntity(finderClass: BadgeType::class)]
class BadgeClass implements CrudEntityInterface
{
    use Id;
    use Uuid;
    use Name;
    use Description;
    use CreatedAt;
    use UpdatedAt;
    use Color;
    use Template;
    use Archived;

    #[ORM\Column]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $criteria = null;

    /**
     * @var Collection<int, Rule>
     */
    #[ORM\OneToMany(targetEntity: Rule::class, mappedBy: 'badge', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $rules;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    private ?Organization $issuer = null;

    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private ?Workspace $workspace = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $durationValidation = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hideRecipients = false;

    /**
     * Allows whose owns the badge to grant it to others.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $issuingPeer = false;

    /**
     * Notifies users when they are granted the badge.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $notifyGrant = false;

    public function __construct()
    {
        $this->refreshUuid();

        $this->rules = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return [];
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Set the value of Image.
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    public function getCriteria(): ?string
    {
        return $this->criteria;
    }

    public function setCriteria(?string $criteria): void
    {
        $this->criteria = $criteria;
    }

    public function getIssuer(): ?Organization
    {
        return $this->issuer;
    }

    public function setIssuer(?Organization $issuer = null): void
    {
        $this->issuer = $issuer;
    }

    public function setDurationValidation(int $duration): void
    {
        $this->durationValidation = $duration;
    }

    public function getDurationValidation(): int
    {
        if (!$this->durationValidation) {
            // 100 years validation !
            return 365 * 100;
        }

        return $this->durationValidation;
    }

    public function setWorkspace(Workspace $workspace = null): void
    {
        $this->workspace = $workspace;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setHideRecipients(bool $hideRecipients): void
    {
        $this->hideRecipients = $hideRecipients;
    }

    public function getHideRecipients(): bool
    {
        return $this->hideRecipients;
    }

    public function setIssuingPeer(bool $issuingPeer): void
    {
        $this->issuingPeer = $issuingPeer;
    }

    public function hasIssuingPeer(): bool
    {
        return $this->issuingPeer;
    }

    public function setNotifyGrant(bool $notifyGrant): void
    {
        $this->notifyGrant = $notifyGrant;
    }

    public function getNotifyGrant(): bool
    {
        return $this->notifyGrant;
    }

    /**
     * @return Rule[]|ArrayCollection
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function addRule(Rule $rule): void
    {
        if (!$this->rules->contains($rule)) {
            $this->rules->add($rule);
            $rule->setBadge($this);
        }
    }

    public function removeRule(Rule $rule): void
    {
        if ($this->rules->contains($rule)) {
            $this->rules->removeElement($rule);
            $rule->setBadge(null);
        }
    }

    // this is for security checks
    public function getOrganizations(): array
    {
        if (!empty($this->issuer)) {
            return [$this->issuer];
        }

        return [];
    }
}

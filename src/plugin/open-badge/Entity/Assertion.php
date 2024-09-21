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

use Doctrine\DBAL\Types\Types;
use Claroline\OpenBadgeBundle\Repository\AssertionRepository;
use DateTimeInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Represents the obtaining of a BadgeClass by a User.
 *
 *
 */
#[ORM\Table(name: 'claro__open_badge_assertion')]
#[ORM\Entity(repositoryClass: AssertionRepository::class)]
class Assertion
{
    use Id;
    use Uuid;

    
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $recipient = null;

    
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: BadgeClass::class)]
    private ?BadgeClass $badge = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeInterface $issuedOn = null;

    /**
     * @var Collection<int, Evidence>
     */
    #[ORM\OneToMany(targetEntity: Evidence::class, mappedBy: 'assertion')]
    private Collection $evidences;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $revoked = false;

    public function __construct()
    {
        $this->refreshUuid();

        $this->evidences = new ArrayCollection();
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function getBadge(): ?BadgeClass
    {
        return $this->badge;
    }

    public function setBadge(BadgeClass $badge): void
    {
        $this->badge = $badge;
    }

    public function getIssuedOn(): DateTimeInterface
    {
        return $this->issuedOn;
    }

    public function setIssuedOn(DateTimeInterface $issuedOn): void
    {
        $this->issuedOn = $issuedOn;
    }

    public function getEvidences(): Collection
    {
        return $this->evidences;
    }

    public function setEvidences(Collection $evidences): void
    {
        $this->evidences = $evidences;
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function setRevoked(bool $revoked): void
    {
        $this->revoked = $revoked;
    }
}

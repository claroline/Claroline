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
 * @ORM\Entity(repositoryClass="Claroline\OpenBadgeBundle\Repository\AssertionRepository")
 * @ORM\Table(name="claro__open_badge_assertion")
 */
class Assertion
{
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?User $recipient = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\BadgeClass")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?BadgeClass $badge = null;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private ?\DateTimeInterface $issuedOn = null;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\OpenBadgeBundle\Entity\Evidence", mappedBy="assertion")
     */
    private Collection $evidences;

    /**
     * @ORM\Column(type="boolean")
     */
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

    public function getIssuedOn(): \DateTimeInterface
    {
        return $this->issuedOn;
    }

    public function setIssuedOn(\DateTimeInterface $issuedOn): void
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

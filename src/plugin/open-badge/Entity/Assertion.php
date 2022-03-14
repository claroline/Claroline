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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Represents the obtaining of a BadgeClass by a User.
 *
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_assertion")
 */
class Assertion
{
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var User
     */
    private $recipient;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\BadgeClass")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var BadgeClass
     */
    private $badge;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\VerificationObject")
     *
     * @var VerificationObject
     */
    private $verification;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    private $issuedOn;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\OpenBadgeBundle\Entity\Evidence", mappedBy="assertion")
     *
     * @var Evidence[]|ArrayCollection
     */
    private $evidences;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $narrative;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $revoked = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $revocationReason;

    public function __construct()
    {
        $this->refreshUuid();

        $this->evidences = new ArrayCollection();
    }

    /**
     * Get the value of Recipient.
     *
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set the value of Recipient.
     *
     * @param User $recipient
     *
     * @return self
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get the value of Badge.
     *
     * @return BadgeClass
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * Set the value of Badge.
     *
     * @param BadgeClass $badge
     *
     * @return self
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Get the value of Verification.
     *
     * @return VerificationObject
     */
    public function getVerification()
    {
        return $this->verification;
    }

    /**
     * Set the value of Verification.
     *
     * @param VerificationObject $verification
     *
     * @return self
     */
    public function setVerification($verification)
    {
        $this->verification = $verification;

        return $this;
    }

    /**
     * Get the value of Issued On.
     *
     * @return \DateTime
     */
    public function getIssuedOn()
    {
        return $this->issuedOn;
    }

    /**
     * Set the value of Issued On.
     *
     * @param \DateTime $issuedOn
     *
     * @return self
     */
    public function setIssuedOn($issuedOn)
    {
        $this->issuedOn = $issuedOn;

        return $this;
    }

    /**
     * Get the value of Image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of Image.
     *
     * @param string $image
     *
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of Evidences.
     *
     * @return Evidence[]|ArrayCollection
     */
    public function getEvidences()
    {
        return $this->evidences;
    }

    /**
     * Set the value of Evidences.
     *
     * @param Evidence[]|ArrayCollection $evidences
     *
     * @return self
     */
    public function setEvidences($evidences)
    {
        $this->evidences = $evidences;

        return $this;
    }

    /**
     * Get the value of Narrative.
     *
     * @return string
     */
    public function getNarrative()
    {
        return $this->narrative;
    }

    /**
     * Set the value of Narrative.
     *
     * @param string $narrative
     *
     * @return self
     */
    public function setNarrative($narrative)
    {
        $this->narrative = $narrative;

        return $this;
    }

    /**
     * Get the value of Revoked.
     *
     * @return bool
     */
    public function getRevoked()
    {
        return $this->revoked;
    }

    /**
     * Set the value of Revoked.
     *
     * @param bool $revoked
     *
     * @return self
     */
    public function setRevoked($revoked)
    {
        $this->revoked = $revoked;

        return $this;
    }

    /**
     * Get the value of Revocation Reason.
     *
     * @return string
     */
    public function getRevocationReason()
    {
        return $this->revocationReason;
    }

    /**
     * Set the value of Revocation Reason.
     *
     * @param string $revocationReason
     *
     * @return self
     */
    public function setRevocationReason($revocationReason)
    {
        $this->revocationReason = $revocationReason;

        return $this;
    }
}

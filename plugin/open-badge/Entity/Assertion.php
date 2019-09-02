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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_assertion")
 */
class Assertion
{
    use Uuid;
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $recipient;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\BadgeClass", inversedBy="assertions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $badge;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\VerificationObject")
     */
    private $verification;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $issuedOn;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\Evidence")
     */
    private $evidences;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $narrative;

    /**
     * @ORM\Column(type="boolean")
     */
    private $revoked = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $revocationReason;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get the value of Recipient.
     *
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set the value of Recipient.
     *
     * @param mixed recipient
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
     * @return mixed
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * Set the value of Badge.
     *
     * @param mixed badge
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
     * @return mixed
     */
    public function getVerification()
    {
        return $this->verification;
    }

    /**
     * Set the value of Verification.
     *
     * @param mixed verification
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
     * @return mixed
     */
    public function getIssuedOn()
    {
        return $this->issuedOn;
    }

    /**
     * Set the value of Issued On.
     *
     * @param mixed issuedOn
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
     * @param string image
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
     * @return mixed
     */
    public function getEvidences()
    {
        return $this->evidences;
    }

    /**
     * Set the value of Evidences.
     *
     * @param mixed evidences
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
     * @return mixed
     */
    public function getNarrative()
    {
        return $this->narrative;
    }

    /**
     * Set the value of Narrative.
     *
     * @param mixed narrative
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
     * @return mixed
     */
    public function getRevoked()
    {
        return $this->revoked;
    }

    /**
     * Set the value of Revoked.
     *
     * @param mixed revoked
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
     * @return mixed
     */
    public function getRevocationReason()
    {
        return $this->revocationReason;
    }

    /**
     * Set the value of Revocation Reason.
     *
     * @param mixed revocationReason
     *
     * @return self
     */
    public function setRevocationReason($revocationReason)
    {
        $this->revocationReason = $revocationReason;

        return $this;
    }
}

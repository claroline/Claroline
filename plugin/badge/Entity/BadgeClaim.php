<?php

namespace Icap\BadgeBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Type.
 *
 * @ORM\Table(
 *      name="claro_badge_claim",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="badge_claim_unique",columns={"user_id", "badge_id"})}
 * )
 * @ORM\Entity(repositoryClass="Icap\BadgeBundle\Repository\BadgeClaimRepository")
 */
class BadgeClaim
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
      * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var Badge
     *
     * @ORM\ManyToOne(targetEntity="Icap\BadgeBundle\Entity\Badge", inversedBy="badgeClaims")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $badge;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="claimed_at", type="datetime", nullable=false)
     */
    protected $claimedAt;

    /**
     * @param int $id
     *
     * @return UserBadge
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param User $user
     *
     * @return UserBadge
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Badge $badge
     *
     * @return BadgeClaim
     */
    public function setBadge(Badge $badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * @return Badge
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @param \DateTime $claimedAt
     *
     * @return BadgeClaim
     */
    public function setClaimedAt($claimedAt)
    {
        $this->claimedAt = $claimedAt;
    }

    /**
     * @return \DateTime
     */
    public function getClaimedAt()
    {
        return $this->claimedAt;
    }
}

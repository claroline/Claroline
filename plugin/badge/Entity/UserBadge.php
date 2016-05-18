<?php

namespace Icap\BadgeBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Type.
 *
 * @ORM\Table(
 *      name="claro_user_badge",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="user_badge_unique",columns={"user_id", "badge_id"})}
 * )
 * @ORM\Entity(repositoryClass="Icap\BadgeBundle\Repository\UserBadgeRepository")
 */
class UserBadge
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
     * @ORM\ManyToOne(targetEntity="Icap\BadgeBundle\Entity\Badge", inversedBy="userBadges")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $badge;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="issued_at", type="datetime", nullable=false)
     */
    protected $issuedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
      * @ORM\JoinColumn(name="issuer_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $issuer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expired_at", type="datetime", nullable=true)
     */
    protected $expiredAt;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_shared", type="boolean")
     */
    protected $isShared = false;

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
     * @return UserBadge
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
     * @return \Datetime
     */
    public function getIssuedAt()
    {
        return $this->issuedAt;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User|null $issuer
     *
     * @return UserBadge
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @param \DateTime $expiredAt
     *
     * @return UserBadge
     */
    public function setExpiredAt($expiredAt)
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        if (null !== $this->expiredAt) {
            return $this->expiredAt <= (new \DateTime());
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isExpiring()
    {
        return null !== $this->expiredAt;
    }

    /**
     * @param string $comment
     *
     * @return UserBadge
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return bool
     */
    public function isIsShared()
    {
        return $this->isShared;
    }

    /**
     * @param bool $isShared
     *
     * @return UserBadge
     */
    public function setIsShared($isShared)
    {
        $this->isShared = $isShared;

        return $this;
    }
}

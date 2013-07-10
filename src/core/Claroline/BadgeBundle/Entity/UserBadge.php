<?php

namespace Claroline\BadgeBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Type
 *
 * @ORM\Table(
 *      name="claro_user_badge",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="user_badge_unique",columns={"user_id", "badge_id"})}
 * )
 * @ORM\Entity(repositoryClass="Claroline\BadgeBundle\Repository\UserBadgeRepository")
 */
class UserBadge
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", inversedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
    */
    protected $user;

    /**
     * @var Badge
     *
     * @ORM\ManyToOne(targetEntity="Claroline\BadgeBundle\Entity\Badge", inversedBy="badge")
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
     * @return UserBadge
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @param \DateTime $issuedAt
     *
     * @return UserBadge
     */
    public function setIssuedAt(\DateTime $issuedAt)
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getIssuedAt()
    {
        return $this->issuedAt;
    }
}

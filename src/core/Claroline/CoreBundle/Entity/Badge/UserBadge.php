<?php

namespace Claroline\CoreBundle\Entity\Badge;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 *
 * @ORM\Table(
 *      name="claro_user_badge",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="user_badge_unique",columns={"user_id", "badge_id"})}
 * )
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserBadgeRepository")
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
    */
    protected $user;

    /**
     * @var Badge
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Badge\Badge")
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
    */
    protected $badge;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issued_at", type="datetime", nullable=false)
     */
    protected $issuedAt;

    /**
     * @param int $id
     *
     * @return Id
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
     * @return User
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
     * @return Badge
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
     * @param \DateTime $issuedAt
     *
     * @return IssuedAt
     */
    public function setIssuedAt(\DateTime $issuedAt)
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\datetime
     */
    public function getIssuedAt()
    {
        return $this->issuedAt;
    }
}

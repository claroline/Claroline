<?php

namespace Claroline\CoreBundle\Entity\Badge;

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
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Badge\UserBadgeRepository")
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", inversedBy="userBadges")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
    */
    protected $user;

    /**
     * @var Badge
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Badge\Badge", inversedBy="userBadges")
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
     * @var User $issuer
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", inversedBy="issuedBadges")
     * @ORM\JoinColumn(name="issuer_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
    */
    protected $issuer;

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
     * @return \Datetime
     */
    public function getIssuedAt()
    {
        return $this->issuedAt;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getIssuer()
    {
        return $this->issuer;
    }
}

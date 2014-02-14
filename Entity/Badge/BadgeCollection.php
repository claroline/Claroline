<?php

namespace Claroline\CoreBundle\Entity\Badge;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="claro_badge_collection", uniqueConstraints={@ORM\UniqueConstraint(name="shared_id_idx", columns={"shared_id"})})
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Badge\BadgeCollectionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BadgeCollection
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Assert\NotNull()
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Badge\Badge")
     * @ORM\JoinTable(name="claro_badge_collection_badges")
     */
    protected $badges;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_shared", type="boolean")
     */
    protected $isShared = false;

    /**
     * @var string
     *
     * @ORM\Column(name="shared_id", type="string", nullable=true)
     */
    protected $sharedId;

    public function __construct()
    {
        $this->badges = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $this->sharedId = md5($this->getUser()->getUsername() . time() . $this->getName());
    }

    /**
     * @param int $id
     *
     * @return BadgeCollection
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
     * @param string $name
     *
     * @return BadgeCollection
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Badge[] $badges
     *
     * @return BadgeCollection
     */
    public function setBadges($badges)
    {
        $this->badges = $badges;

        return $this;
    }

    /**
     * @return Badge[]
     */
    public function getBadges()
    {
        return $this->badges;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return BadgeCollection
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param bool $isShared
     *
     * @return BadgeCollection
     */
    public function setIsShared($isShared)
    {
        $this->isShared = $isShared;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isIsShared()
    {
        return $this->isShared;
    }

    /**
     * @param string $sharedId
     *
     * @return BadgeCollection
     */
    public function setSharedId($sharedId)
    {
        $this->sharedId = $sharedId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSharedId()
    {
        return $this->sharedId;
    }
}
 
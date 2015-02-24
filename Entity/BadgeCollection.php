<?php

namespace Icap\BadgeBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="claro_badge_collection", uniqueConstraints={@ORM\UniqueConstraint(name="slug_idx", columns={"slug"})})
 * @ORM\Entity(repositoryClass="Icap\BadgeBundle\Repository\BadgeCollectionRepository")
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
     * @var string $slug
     *
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    protected $slug;

    /**
     * @ORM\ManyToMany(targetEntity="Icap\BadgeBundle\Entity\Badge")
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

    public function __construct()
    {
        $this->badges = new ArrayCollection();
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
     * @param string $slug
     *
     * @return BadgeCollection
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
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
}
 
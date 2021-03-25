<?php

namespace Claroline\CoreBundle\Entity\ConnectionMessage;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\AppBundle\Entity\Restriction\Hidden;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConnectionMessage.
 *
 * @ORM\Table(name="claro_connection_message")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ConnectionMessage\ConnectionMessageRepository")
 */
class ConnectionMessage
{
    // identifiers
    use Id;
    use Uuid;
    // restrictions
    use Hidden;
    use AccessibleFrom;
    use AccessibleUntil;

    const TYPE_ONCE = 'once';
    const TYPE_ALWAYS = 'always';
    const TYPE_DISCARD = 'discard';

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="message_type")
     *
     * @var string
     */
    protected $type = self::TYPE_ONCE;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $locked = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\ConnectionMessage\Slide",
     *     mappedBy="message",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @var ArrayCollection|Slide[]
     */
    private $slides;

    /**
     * List of roles the message is destined to.
     *
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinTable(name="claro_connection_message_role")
     *
     * @var ArrayCollection|Role[]
     */
    private $roles;

    /**
     * List of users for who the message doesn't have to be displayed anymore.
     *
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="claro_connection_message_user")
     *
     * @var ArrayCollection|User[]
     */
    private $users;

    /**
     * ConnectionMessage constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->slides = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Set locked.
     *
     * @param bool $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * Get slides.
     *
     * @return ArrayCollection
     */
    public function getSlides()
    {
        return $this->slides;
    }

    /**
     * Add a slide to the message.
     */
    public function addSlide(Slide $slide)
    {
        if (!$this->slides->contains($slide)) {
            $this->slides->add($slide);
            $slide->setMessage($this);
        }
    }

    /**
     * Remove a slide from the message.
     */
    public function removeSlide(Slide $slide)
    {
        if ($this->slides->contains($slide)) {
            $this->slides->removeElement($slide);
            $slide->setMessage(null);
        }
    }

    /**
     * Get roles.
     *
     * @return ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add a role to the message.
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * Remove a role from the message.
     */
    public function removeRole(Role $role)
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    /**
     * Remove all roles from message.
     */
    public function emptyRoles()
    {
        $this->roles->clear();
    }

    /**
     * Get users.
     *
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add an user to the message.
     */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    /**
     * Remove an user from the message.
     */
    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }
}

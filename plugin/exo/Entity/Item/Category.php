<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\CategoryRepository")
 * @ORM\Table(name="ujm_category")
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    use UuidTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="locker", type="boolean")
     */
    private $default = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @deprecated use setName() instead
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->name = $value;
    }

    /**
     * Get value.
     *
     * @deprecated use getName() instead
     *
     * @return string
     */
    public function getValue()
    {
        return $this->name;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Is it the default user category ?
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Set default.
     *
     * @param bool $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * get locker.
     *
     * @deprecated use isDefault() instead
     *
     * @return bool
     */
    public function getLocker()
    {
        return $this->default;
    }

    /**
     * set locker.
     *
     * @deprecated use setDefault() instead
     *
     * @param bool $locker
     */
    public function setLocker($locker)
    {
        $this->default = $locker;
    }

    /**
     * @deprecated
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}

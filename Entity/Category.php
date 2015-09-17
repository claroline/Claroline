<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;

/**
 * UJM\ExoBundle\Entity\Category.
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

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var bool
     *
     * @ORM\Column(name="locker", type="boolean")
     */
    private $locker = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

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
     * Set value.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
    /**
     * get locker.
     *
     * @return bool
     */
    public function getLocker()
    {
        return $this->locker;
    }

    /**
     * set locker.
     *
     * @param bool $locker
     */
    public function setLocker($locker)
    {
        $this->locker = $locker;
    }
}

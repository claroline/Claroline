<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User2Path
 *
 * @ORM\Table(name="innova_user2path")
 * @ORM\Entity
 */
class User2Path
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;


    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
    */
    private $user;


    /**
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Path")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
    */
    private $path;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return User2Path
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     * Status : start/continue/finished
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user
     *
     * @param  \Claroline\CoreBundle\Entity\User $user
     * @return Content2Type
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * Set path
     *
     * @param  Innova\PathBundle\Entity\Path $path
     * @return Content2Type
     */
    public function setPath(Path $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return Innova\PathBundle\Entity\Path
     */
    public function getPath()
    {
        return $this->path;
    }
}

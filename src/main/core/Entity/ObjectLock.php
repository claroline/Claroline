<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_object_lock", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *          name="unique",
 *          columns={"object_uuid", "object_class"}
 *     )
 * })
 */
class ObjectLock
{
    use Id;

    /**
     * @ORM\Column(name="object_uuid", type="string")
     *
     * @var string
     */
    private $objectUuid;

    /**
     * @ORM\Column(name="object_class")
     *
     * @var string
     */
    private $objectClass;

    /**
     * @ORM\Column(name="locked", type="boolean")
     *
     * @var bool
     */
    private $locked = false;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    protected $lastModification;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL", nullable=true)
     *
     * @var User
     */
    protected $lastUser;

    /**
     * Get the value of Object Uuid.
     *
     * @return string
     */
    public function getObjectUuid()
    {
        return $this->objectUuid;
    }

    /**
     * Set the value of Object Uuid.
     *
     * @param string objectUuid
     *
     * @return self
     */
    public function setObjectUuid($objectUuid)
    {
        $this->objectUuid = $objectUuid;

        return $this;
    }

    /**
     * Get the value of Object Class.
     *
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Set the value of Object Class.
     *
     * @param string objectClass
     *
     * @return self
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * Get the value of Locked.
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * Set the value of Locked.
     *
     * @param bool locked
     *
     * @return self
     */
    public function setLocked(bool $locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get the value of Last Modification.
     *
     * @return \DateTime
     */
    public function getLastModification()
    {
        return $this->lastModification;
    }

    /**
     * Set the value of Last Modification.
     *
     * @param \DateTime lastModification
     *
     * @return self
     */
    public function setLastModification(\DateTime $lastModification)
    {
        $this->lastModification = $lastModification;

        return $this;
    }

    /**
     * Get the value of Last User.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->lastUser;
    }

    /**
     * Set the value of Last User.
     *
     * @param User lastUser
     *
     * @return self
     */
    public function setUser(User $lastUser)
    {
        $this->lastUser = $lastUser;

        return $this;
    }
}

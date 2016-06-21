<?php

namespace Icap\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Icap\NotificationBundle\Repository\FollowerResourceRepository")
 * @ORM\Table(name="icap__notification_follower_resource")
 */
class FollowerResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $hash;

    /**
     * @ORM\Column(type="string", name="resource_class", length=255)
     */
    protected $resourceClass;

    /**
     * @ORM\Column(type="integer", name="resource_id")
     */
    protected $resourceId;

    /**
     * @ORM\Column(type="integer", name="follower_id", nullable=false)
     */
    protected $followerId;

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
     * Set hash.
     *
     * @param string $hash
     *
     * @return FollowerResource
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set resourceClass.
     *
     * @param string $resourceClass
     *
     * @return FollowerResource
     */
    public function setResourceClass($resourceClass)
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    /**
     * Get resourceClass.
     *
     * @return string
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * Set resourceId.
     *
     * @param int $resourceId
     *
     * @return FollowerResource
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * Get resourceId.
     *
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Set followerId.
     *
     * @param int $followerId
     *
     * @return FollowerResource
     */
    public function setFollowerId($followerId)
    {
        $this->followerId = $followerId;

        return $this;
    }

    /**
     * Get followerId.
     *
     * @return int
     */
    public function getFollowerId()
    {
        return $this->followerId;
    }
}

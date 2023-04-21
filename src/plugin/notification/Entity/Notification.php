<?php

namespace Icap\NotificationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Icap\NotificationBundle\Repository\NotificationRepository")
 * @ORM\Table(name="icap__notification")
 */
class Notification
{
    use Id;

    /**
     * @ORM\Column(type="datetime", name="creation_date")
     * @Gedmo\Timestampable(on="create")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="integer", name="user_id", nullable=true)
     */
    private $userId;

    /**
     * @ORM\Column(type="integer", name="resource_id", nullable=true)
     */
    private $resourceId;

    /**
     * @ORM\Column(type="string", name="icon_key", nullable=true)
     */
    private $iconKey;

    /**
     * @ORM\Column(type="string", name="action_key")
     */
    private $actionKey;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $details;

    private $iconColor = null;

    /**
     * Get creationDate.
     *
     * @return string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return Notification
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set resourceId.
     *
     * @param int $resourceId
     *
     * @return Notification
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
     * Set iconKey.
     *
     * @param string $iconKey
     *
     * @return Notification
     */
    public function setIconKey($iconKey)
    {
        $this->iconKey = $iconKey;

        return $this;
    }

    /**
     * Get $iconKey.
     *
     * @return string
     */
    public function getIconKey()
    {
        return $this->iconKey;
    }

    /**
     * Set actionKey.
     *
     * @param string $actionKey
     *
     * @return Notification
     */
    public function setActionKey($actionKey)
    {
        $this->actionKey = $actionKey;

        return $this;
    }

    /**
     * Get $actionKey.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this->actionKey;
    }

    /**
     * Get $iconColor.
     *
     * @return string
     */
    public function getIconColor()
    {
        return $this->iconColor;
    }

    /**
     * Set $iconColor.
     *
     * @param string $iconColor
     *
     * @return notification
     */
    public function setIconColor($iconColor)
    {
        $this->iconColor = $iconColor;

        return $this;
    }

    /**
     * @return string letter
     */
    public function getIconLetter()
    {
        $letter = null;
        if (!empty($this->iconKey)) {
            $letter = $this->iconKey[0];
        }

        return $letter;
    }

    /**
     * Set details.
     *
     * @param array $details
     *
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }
}

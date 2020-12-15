<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 5/12/15
 */

namespace Icap\SocialmediaBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\SocialmediaBundle\Entity\CommentAction;

class LogSocialmediaCommentEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-icap_socialmedia-comment_action';
    protected $details;
    protected $userIds;

    public function __construct(CommentAction $comment, $userIds)
    {
        $this->details = array();
        $this->userIds = $userIds;

        parent::__construct($comment->getResource(), $this->details);
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers()
    {
        return false;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        $creatorId = $this->getResource()->getCreator()->getId();
        if (!in_array($creatorId, $this->userIds)) {
            $this->userIds[] = $creatorId;
        }

        return $this->userIds;
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return array();
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this::ACTION;
    }

    /**
     * Get iconKey string.
     *
     * @return string
     */
    public function getIconKey()
    {
        return 'socialmedia';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $resource = $this->getResource();
        $notificationDetails = array_merge($this->details, array());
        $notificationDetails['resource'] = array(
            'id' => $resource->getId(),
            'name' => $resource->getName(),
            'type' => $resource->getResourceType()->getName(),
        );

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        return true;
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}

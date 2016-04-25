<?php

namespace Icap\BadgeBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;

class LogBadgeAwardEvent extends LogGenericEvent implements NotifiableInterface
{
    const ACTION = 'badge-awarding';

    /**
     * @var \Icap\BadgeBundle\Entity\Badge
     */
    private $badge;

    /**
     * @param Badge $badge
     * @param User  $receiver
     */
    public function __construct(Badge $badge, User $receiver, $doer)
    {
        if (null === $doer) {
            $this->doer = LogGenericEvent::PLATFORM_EVENT_TYPE;
        }

        $this->badge = $badge;

        parent::__construct(
            self::ACTION,
            array(
                'badge' => array(
                    'id' => $badge->getId(),
                ),
                'receiverUser' => array(
                    'lastName' => $receiver->getLastName(),
                    'firstName' => $receiver->getFirstName(),
                ),
            ),
            $receiver,
            null,
            null,
            null,
            $badge->getWorkspace()
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers()
    {
        return true;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        return array($this->getReceiver()->getId());
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
        return 'badge';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $receiver = $this->getReceiver();
        $workspace = $this->badge->getWorkspace() ? $this->badge->getWorkspace()->getId() : null;

        $notificationDetails = array(
            'workspace' => $workspace,
            'badge' => array(
                'id' => $this->badge->getId(),
                'name' => $this->badge->getName(),
                'slug' => $this->badge->getSlug(),
            ),
            'receiver' => array(
                'id' => $receiver->getId(),
                'publicUrl' => $receiver->getPublicUrl(),
                'lastName' => $receiver->getLastName(),
                'firstName' => $receiver->getFirstName(),
            ),
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
}

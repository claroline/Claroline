<?php

namespace Claroline\CoreBundle\Event\Log;

interface NotifiableInterface
{
    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers();

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds();

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds();

    /**
     * Get doer Object.
     *
     * @return doer
     */
    public function getDoer();

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey();

    /**
     * Get iconKey string.
     *
     * @return string
     */
    public function getIconKey();

    /**
     * Get resource Object.
     *
     * @return resource
     */
    public function getResource();

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails();

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify();
}

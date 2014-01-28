<?php

namespace Icap\NotificationBundle\Entity;

interface NotifiableInterface
{
    /**
     * Get sendToFollowers boolean.
     *
     * @return boolean
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
     * Get doerId integer
     *
     * @return integer
     */
    public function getDoerId();

    /**
     * Get resourceId
     *
     * @return integer
     */
    public function getResourceId();

    /**
     * Get resource class
     *
     * @return string
     */
    public function getResourceClass();

    /**
     * Get details
     *
     * @return array
     */
    public function getNotificationDetails();

    /**
     * Get if event is allowed to create notification or not
     *
     * @return boolean
     */
    public function isAllowedToNotify();
}
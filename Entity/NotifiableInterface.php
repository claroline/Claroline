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
     * Get date date.
     *
     * @return date
     */
    public function getDate();

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
     * Get targetUrl string.
     *
     * @return string
     */
    public function getTargetUrl();

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
     * Get resourceClass
     *
     * @return string
     */
    public function getResourceClass();
}
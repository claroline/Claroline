<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/13/15
 */

namespace Icap\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotificationPluginConfiguration.
 *
 * @ORM\Entity()
 * @ORM\Table(name="icap__notification_plugin_configuration")
 */
class NotificationPluginConfiguration
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", name="dropdown_items")
     */
    protected $dropdownItems = 10;

    /**
     * @ORM\Column(type="integer", name="max_per_page")
     */
    protected $maxPerPage = 50;

    /**
     * @ORM\Column(type="boolean", name="purge_enabled")
     */
    protected $purgeEnabled = true;

    /**
     * @ORM\Column(type="integer", name="purge_after_days")
     */
    protected $purgeAfterDays = 60;

    /**
     * @ORM\Column(type="datetime", name="last_purge_date", nullable=true)
     */
    protected $lastPurgeDate;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDropdownItems()
    {
        return $this->dropdownItems;
    }

    /**
     * @param $dropdownItems
     *
     * @return $this
     */
    public function setDropdownItems($dropdownItems)
    {
        $this->dropdownItems = $dropdownItems;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * @param mixed $maxPerPage
     *
     * @return $this
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurgeEnabled()
    {
        return $this->purgeEnabled;
    }

    /**
     * @param mixed $purgeEnabled
     *
     * @return $this
     */
    public function setPurgeEnabled($purgeEnabled)
    {
        $this->purgeEnabled = $purgeEnabled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurgeAfterDays()
    {
        return $this->purgeAfterDays;
    }

    /**
     * @param mixed $purgeAfterDays
     */
    public function setPurgeAfterDays($purgeAfterDays)
    {
        $this->purgeAfterDays = $purgeAfterDays;
    }

    /**
     * @return mixed
     */
    public function getLastPurgeDate()
    {
        return $this->lastPurgeDate;
    }

    /**
     * @param mixed $lastPurgeDate
     */
    public function setLastPurgeDate($lastPurgeDate)
    {
        $this->lastPurgeDate = $lastPurgeDate;
    }
}

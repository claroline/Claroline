<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/8/15
 */

namespace Icap\NotificationBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Icap\NotificationBundle\Repository\NotificationUserParametersRepository")
 * @ORM\Table(name="icap__notification_user_parameters")
 */
class NotificationUserParameters
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(type="array", name="display_enabled_types")
     */
    protected $displayEnabledTypes = [];

    /**
     * @ORM\Column(type="array", name="rss_enabled_types")
     */
    protected $rssEnabledTypes = [];

    /**
     * @ORM\Column(type="string", name="rss_id", unique=true)
     */
    protected $rssId;

    /**
     * @var bool
     */
    protected $isNew = false;

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
    public function getUserId()
    {
        return $this->user->getId();
    }

    /**
     * @param mixed $userId
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param mixed $userId
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getDisplayEnabledTypes()
    {
        return $this->displayEnabledTypes;
    }

    /**
     * @param mixed $displayEnabledTypes
     */
    public function setDisplayEnabledTypes($displayEnabledTypes)
    {
        $this->displayEnabledTypes = $displayEnabledTypes;
    }

    /**
     * @return mixed
     */
    public function getRssEnabledTypes()
    {
        return $this->rssEnabledTypes;
    }

    /**
     * @param mixed $rssEnabledTypes
     */
    public function setRssEnabledTypes($rssEnabledTypes)
    {
        $this->rssEnabledTypes = $rssEnabledTypes;
    }

    /**
     * @return mixed
     */
    public function getRssId()
    {
        return $this->rssId;
    }

    /**
     * @param mixed $rssId
     */
    public function setRssId($rssId)
    {
        $this->rssId = $rssId;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @param bool $isNew
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }
}

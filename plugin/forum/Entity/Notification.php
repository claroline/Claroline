<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_notification")
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\ForumBundle\Entity\Forum")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $forum;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="self_activation", type="boolean", options={"default": 1})
     */
    protected $selfActivation = true;

    public function getId()
    {
        return $this->id;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setForum(Forum $forum)
    {
        $this->forum = $forum;
    }

    public function getForum()
    {
        return $this->forum;
    }

    public function getSelfActivation()
    {
        return $this->selfActivation;
    }

    public function setSelfActivation($selfActivation)
    {
        $this->selfActivation = $selfActivation;
    }
}

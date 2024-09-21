<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity\Validation;

use Doctrine\DBAL\Types\Types;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User as ClarolineUser;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_forum_user')]
#[ORM\Entity]
class User
{
    use Id;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ClarolineUser::class, cascade: ['persist', 'remove'])]
    protected ?ClarolineUser $user = null;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Forum::class, cascade: ['persist'])]
    protected ?Forum $forum = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected $access = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected $banned = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected $notified = false;

    public function setUser(ClarolineUser $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setForum($forum)
    {
        $this->forum = $forum;
    }

    public function getForum()
    {
        return $this->forum;
    }

    public function setAccess($bool)
    {
        $this->access = $bool;
    }

    public function getAccess()
    {
        return $this->access;
    }

    public function setBanned($bool)
    {
        $this->banned = $bool;
    }

    public function isBanned()
    {
        return $this->banned;
    }

    public function setNotified($bool)
    {
        $this->notified = $bool;
    }

    public function isNotified()
    {
        return $this->notified;
    }
}

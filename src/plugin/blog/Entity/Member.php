<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\BlogBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Icap\BlogBundle\Repository\MemberRepository;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User as User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'icap__blog_member')]
#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    use Id;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    protected ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Blog::class, inversedBy: 'members', cascade: ['persist'])]
    protected ?Blog $blog = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected $trusted = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected $banned = false;

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setBlog($blog)
    {
        $this->blog = $blog;
    }

    public function getBlog()
    {
        return $this->blog;
    }

    public function setTrusted($bool)
    {
        $this->trusted = $bool;
    }

    public function getTrusted()
    {
        return $this->trusted;
    }

    public function setBanned($bool)
    {
        $this->banned = $bool;
    }

    public function isBanned()
    {
        return $this->banned;
    }
}

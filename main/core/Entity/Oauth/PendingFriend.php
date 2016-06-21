<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Oauth;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_pending_friend")
 * @ORM\Entity
 */
class PendingFriend
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", nullable=false)
     */
    protected $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", nullable=false)
     */
    protected $host;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     * @ORM\Column(unique=true)
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

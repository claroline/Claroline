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
 * @ORM\Table(name="claro_friend_request")
 * @ORM\Entity
 */
class FriendRequest
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

    /**
     * @var bool
     *
     * @ORM\Column(name="is_activated", type="boolean")
     */
    protected $isActivated = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_authentication", type="boolean", nullable=false)
     */
    protected $allowAuthentication = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="create_user_if_missing", type="boolean", nullable=false)
     */
    protected $createUserIfMissing = false;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Oauth\ClarolineAccess", mappedBy="friendRequest")
     **/
    protected $clarolineAccess;

    /**
     * @ORM\OneToMany(targetEntity="Client", mappedBy="friendRequest", cascade={"remove"})
     */
    protected $clients;

    public function getId()
    {
        return $this->id;
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

    public function setIsActivated($bool)
    {
        $this->isActivated = $bool;
    }

    public function isActivated()
    {
        return $this->isActivated;
    }

    public function getClarolineAccess()
    {
        return $this->clarolineAccess;
    }

    public function setClarolineAccess(ClarolineAccess $access)
    {
        $this->clarolineAccess = $access;
    }

    public function setAllowAuthentication($bool)
    {
        $this->allowAuthentication = $bool;
    }

    public function getAllowAuthentication()
    {
        return $this->allowAuthentication;
    }

    public function setCreateUserIfMissing($bool)
    {
        $this->createUserIfMissing = $bool;
    }

    public function getCreateUserIfMissing()
    {
        return $this->createUserIfMissing;
    }

    public function getClients()
    {
        return $this->clients;
    }
}

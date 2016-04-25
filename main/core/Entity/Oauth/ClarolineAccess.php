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
 * @ORM\Table(name="claro_api_claroline_access")
 * @ORM\Entity
 *
 * This entity stores the list of Accesses generated. It's used to find the access_token wich was
 * returned for a specific randomId.
 */
class ClarolineAccess
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
     * @ORM\Column(name="random_id", type="string", nullable=false)
     */
    protected $randomId;

    /**
     * @var string
     *
     * @ORM\Column(name="secret", type="string", nullable=false)
     */
    protected $secret;

/**
 * @var string
 *
 * @ORM\Column(name="access_token", type="string", nullable=true)
 */
    //token used by the platform. It's pretty much the admin one.
    protected $accessToken;

    /**
     * @var FriendRequest
     *
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Oauth\FriendRequest",
     *     inversedBy="clarolineAccess"
     * )
     * @ORM\JoinColumn(name="friend_request_id", onDelete="SET NULL")
     */
    protected $friendRequest;

    public function getId()
    {
        return $this->id;
    }

    public function setRandomId($randomId)
    {
        $this->randomId = $randomId;
    }

    public function getRandomId()
    {
        return $this->randomId;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setFriendRequest(FriendRequest $request)
    {
        $this->friendRequest = $request;
    }

    public function getFriendRequest()
    {
        return $this->friendRequest;
    }
}

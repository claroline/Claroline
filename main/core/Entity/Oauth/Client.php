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

use Doctrine\Common\Collections\ArrayCollection;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;

/**
 * @ORM\Table(name="claro_api_client")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Oauth\ClientRepository")
 */
class Client extends BaseClient
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
     * @ORM\Column(name="name", type="string", nullable=false)
     * @ORM\Column(unique=true)
     * @Groups({"api_client"})
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="AccessToken", mappedBy="client", cascade={"remove"})
     */
    protected $accessTokens;

    /**
     * @ORM\OneToMany(targetEntity="AuthCode", mappedBy="client", cascade={"remove"})
     */
    protected $authCodes;

    /**
     * @ORM\OneToMany(targetEntity="RefreshToken", mappedBy="client", cascade={"remove"})
     */
    protected $refreshTokens;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_hidden", type="boolean")
     */
    protected $isHidden = false;

    /**
     * @ORM\ManyToOne(targetEntity="FriendRequest", inversedBy="clients")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $friendRequest;

    //for the form
    private $uri;

    /**
     * @Groups({"api_client"})
     * @Accessor(getter="getSecret")
     */
    protected $clientSecret;

    /**
     * @Groups({"api_client"})
     * @Accessor(getter="getConcatRandomId")
     */
    protected $clientId;

    public function __construct()
    {
        parent::__construct();

        $this->accessTokens = new ArrayCollection();
        $this->authCodes = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
    }

    /**
     * @param mixed $name
     *
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $accessTokens
     *
     * @return Client
     */
    public function setAccessTokens($accessTokens)
    {
        $this->accessTokens = $accessTokens;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessTokens()
    {
        return $this->accessTokens;
    }

    /**
     * @param mixed $authCodes
     *
     * @return Client
     */
    public function setAuthCodes($authCodes)
    {
        $this->authCodes = $authCodes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthCodes()
    {
        return $this->authCodes;
    }

    /**
     * @param mixed $refreshTokens
     *
     * @return Client
     */
    public function setRefreshTokens($refreshTokens)
    {
        $this->refreshTokens = $refreshTokens;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRefreshTokens()
    {
        return $this->refreshTokens;
    }

    public function getConcatRandomId()
    {
        return $this->id.'_'.$this->getRandomId();
    }

    public function hide()
    {
        $this->isHidden = true;
    }

    //for the form
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return (isset($this->redirectUris[0])) ? $this->redirectUris[0] : null;
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

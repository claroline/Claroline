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
     * @var boolean
     *
     * @ORM\Column(name="is_hidden", type="boolean")
     */
    protected $isHidden = false;

    public function __construct()
    {
        parent::__construct();

        $this->accessTokens  = new ArrayCollection();
        $this->authCodes     = new ArrayCollection();
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
        return $this->id . '_' . $this->getRandomId();
    }

    public function hide()
    {
        $this->isHidden = true;
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\OAuthBundle\Model;

class Configuration
{
    private $clientId;
    private $clientSecret;
    private $clientActive;
    private $clientTenantDomain = null;
    private $clientVersion = null;
    private $clientForceReauthenticate = false;

    public function __construct($id, $secret, $active, $forceReauthenticate = false, $domain = null, $version = null)
    {
        $this->clientId = $id;
        $this->clientSecret = $secret;
        $this->clientActive = $active;
        $this->clientTenantDomain = $domain;
        $this->clientVersion = $version;
        $this->clientForceReauthenticate = $forceReauthenticate === true;
    }

    /**
     * @param int $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param int $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return int
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param bool $clientActive
     */
    public function setClientActive($clientActive)
    {
        $this->clientActive = $clientActive;
    }

    /**
     * @return bool
     */
    public function isClientActive()
    {
        return $this->clientActive;
    }

    /**
     * @return mixed
     */
    public function getClientTenantDomain()
    {
        return $this->clientTenantDomain;
    }

    /**
     * @param mixed $clientTenantDomain
     *
     * @return $this
     */
    public function setClientTenantDomain($clientTenantDomain)
    {
        $this->clientTenantDomain = $clientTenantDomain;

        return $this;
    }

    public function getClientVersion()
    {
        return $this->clientVersion;
    }

    /**
     * @param null $clientVersion
     *
     * @return $this
     */
    public function setClientVersion($clientVersion)
    {
        $this->clientVersion = $clientVersion;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClientForceReauthenticate()
    {
        return $this->clientForceReauthenticate === true;
    }

    /**
     * @param bool $clientForceReauthenticate
     *
     * @return $this
     */
    public function setClientForceReauthenticate($clientForceReauthenticate)
    {
        $this->clientForceReauthenticate = $clientForceReauthenticate;

        return $this;
    }

    public static function resourceOwners()
    {
        $resourceOwners = ['Facebook', 'Twitter', 'Google', 'Linkedin', 'Windows Live', 'Office 365'];

        return $resourceOwners;
    }
}

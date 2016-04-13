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

    public function __construct($id, $secret, $active)
    {
        $this->clientId = $id;
        $this->clientSecret = $secret;
        $this->clientActive = $active;
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

    public static function resourceOwners()
    {
        $resourceOwners = ['Facebook', 'Twitter', 'Google', 'Linkedin', 'Windows Live'];

        return $resourceOwners;
    }
}

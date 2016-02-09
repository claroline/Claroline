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
     * @param integer $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return integer
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param integer $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return integer
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param boolean $clientActive
     */
    public function setClientActive($clientActive)
    {
        $this->clientActive = $clientActive;
    }

    /**
     * @return boolean
     */
    public function isClientActive()
    {
        return $this->clientActive;
    }

    static public function resourceOwners()
    {
        $resourceOwners = ['Facebook', 'Twitter', 'Google', 'Linkedin'];

        return $resourceOwners;
    }

}

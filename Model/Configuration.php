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

    private $facebookClientId;
    private $facebookClientSecret;
    private $facebookClientActive;

    public function __construct($id, $secret, $active)
    {
        $this->facebookClientId = $id;
        $this->facebookClientSecret = $secret;
        $this->facebookClientActive = $active;
    }


    /**
     * @param integer $facebookClientId
     */
    public function setFacebookClientId($facebookClientId)
    {
        $this->facebookClientId = $facebookClientId;
    }

    /**
     * @return integer
     */
    public function getFacebookClientId()
    {
        return $this->facebookClientId;
    }

    /**
     * @param integer $facebookClientSecret
     */
    public function setFacebookClientSecret($facebookClientSecret)
    {
        $this->facebookClientSecret = $facebookClientSecret;
    }

    /**
     * @return integer
     */
    public function getFacebookClientSecret()
    {
        return $this->facebookClientSecret;
    }

    /**
     * @param boolean $facebookClientActive
     */
    public function setFacebookClientActive($facebookClientActive)
    {
        $this->facebookClientActive = $facebookClientActive;
    }

    /**
     * @return boolean
     */
    public function isFacebookClientActive()
    {
        return $this->facebookClientActive;
    }

}

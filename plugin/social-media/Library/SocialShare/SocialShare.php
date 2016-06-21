<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/23/15
 */

namespace Icap\SocialmediaBundle\Library\SocialShare;

use Icap\SocialmediaBundle\Library\SocialShare\Networks\Facebook;
use Icap\SocialmediaBundle\Library\SocialShare\Networks\GooglePlus;
use Icap\SocialmediaBundle\Library\SocialShare\Networks\LinkedIn;
use Icap\SocialmediaBundle\Library\SocialShare\Networks\NetworkInterface;
use Icap\SocialmediaBundle\Library\SocialShare\Networks\Twitter;

class SocialShare
{
    protected $networks = array();

    public function __construct()
    {
        $this->registerNetwork(new Facebook());
        $this->registerNetwork(new Twitter());
        $this->registerNetwork(new GooglePlus());
        $this->registerNetwork(new LinkedIn());
    }

    public function getNetworks()
    {
        return $this->networks;
    }

    /**
     * @param $name
     *
     * @return NetworkInterface
     */
    public function getNetwork($name)
    {
        return $this->networks[$name];
    }

    private function registerNetwork(NetworkInterface $network)
    {
        $this->networks[$network->getName()] = $network;
    }
}

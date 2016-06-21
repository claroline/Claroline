<?php

/**
 * Description of O365ResponseUser.
 *
 * @author nbr
 */

namespace FormaLibre\OfficeConnectBundle\Library;

class O365ResponseUser
{
    private $responseObj;

    public function __construct($jsonUser)
    {
        $this->responseObj = $jsonUser;
    }

    public function getUsername()
    {
        return $this->responseObj->{'userPrincipalName'};
    }

    public function getResponse()
    {
        return $this->responseObj;
    }

    public function getEmail()
    {
        return $this->responseObj->{'mail'};
    }

    public function getNickname()
    {
        return $this->responseObj->{'givenName'};
    }

    public function getRealName()
    {
        return $this->responseObj->{'surname'};
    }
}

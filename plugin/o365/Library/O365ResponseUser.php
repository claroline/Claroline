<?php

namespace FormaLibre\OfficeConnectBundle\Library;

class O365ResponseUser
{
    private $responseObj;
    private $username;
    private $nickname;
    private $realname;

    public function __construct($jsonUser)
    {
        $this->responseObj = $jsonUser;
        $this->username = $this->responseObj->{'userPrincipalName'};
        $this->email = $this->responseObj->{'mail'};
        $this->nickname = $this->responseObj->{'givenName'};
        $this->realname = $this->responseObj->{'surname'};
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getResponse()
    {
        return $this->responseObj;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function getRealName()
    {
        return $this->realname;
    }

    public function validate()
    {
        $missingProperties = [];

        if ($this->username === null) {
            $missingProperties[] = $this->username;
        }

        if ($this->email === null) {
            $missingProperties[] = $this->email;
        }

        if ($this->nickname === null) {
            $missingProperties[] = $this->nickname;
        }

        if ($this->realname === null) {
            $missingProperties[] = $this->realname;
        }

        return $missingProperties;
    }
}

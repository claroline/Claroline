<?php

namespace FormaLibre\OfficeConnectBundle\Library;

class Configuration
{
    private $officeClientId;
    private $officePassword;
    private $officeAppTenantDomainName;
    private $officeClientActive;

    public function __construct($officeClientId, $officePassword, $officeAppTenantDomainName, $officeClientActive)
    {
        $this->officeClientId = $officeClientId;
        $this->officePassword = $officePassword;
        $this->officeAppTenantDomainName = $officeAppTenantDomainName;
        $this->officeClientActive = $officeClientActive;
    }

    public function setOfficeClientId($officeClientId)
    {
        $this->officeClientId = $officeClientId;
    }

    public function setOfficePassword($officePassword)
    {
        $this->officePassword = $officePassword;
    }

    public function setOfficeAppTenantDomainName($officeAppTenantDomainName)
    {
        $this->officeAppTenantDomainName = $officeAppTenantDomainName;
    }

    public function setOfficeClientActive($officeClientActive)
    {
        $this->officeClientActive = $officeClientActive;
    }

    public function getOfficeClientId()
    {
        return $this->officeClientId;
    }

    public function getOfficePassword()
    {
        return $this->officePassword;
    }

    public function getOfficeAppTenantDomainName()
    {
        return $this->officeAppTenantDomainName;
    }

    public function getOfficeClientActive()
    {
        return $this->officeClientActive;
    }
}

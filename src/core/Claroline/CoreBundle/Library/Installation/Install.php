<?php

namespace Claroline\CoreBundle\Library\Installation;

class Install
{
    private $dbHost;
    private $dbUser;
    private $dbName;
    private $dbPassword;
    private $dbDriver;

    public function setDbHost($dbHost)
    {
        $this->dbHost = $dbHost;
    }

    public function getDbHost()
    {
        return $this->dbHost;
    }

    public function setDbUser($dbUser)
    {
        $this->dbUser = $dbUser;
    }

    public function getDbUser()
    {
        return $this->dbUser;
    }

    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function setDbPassword($dbPassword)
    {
        $this->dbPassword = $dbPassword;
    }

    public function getDbPassword()
    {
        return $this->dbPassword;
    }
    public function getDbDriver()
    {
        return $this->dbDriver;
    }

    public function setDbDriver($dbDriver)
    {
        $this->dbDriver = $dbDriver;
    }
}
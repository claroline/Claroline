<?php

namespace Claroline\CoreBundle\Entity;

/**
 * @todo : this is not an entity -> to be moved to, e.g., library
 */
class Install
{
    private $dbHost;
    private $dbUser;
    private $dbName;
    private $dbPassword;
    private $exist;

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

    public function setExist($exist)
    {
        $this->exist = $exist;
    }

    public function getExist()
    {
        return $this->exist;
    }
}
<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/13/17
 */

namespace Claroline\ExternalSynchronizationBundle\Library\Configuration;

class ExternalSourceConfiguration
{
    const PDO_LIST = [
        'MySQL' => 'pdo_mysql',
        'MS SQL Server' => 'pdo_sqlsrv',
        'PostgreSQL' => 'pdo_pgsql',
    ];

    // General config
    protected $name;
    protected $host;
    protected $port;
    protected $pdo;
    protected $database;
    protected $username;
    protected $password;

    // User table config
    protected $userTable;
    protected $userFirstName;
    protected $userLastName;
    protected $userLogin;
    protected $userCode;
    protected $userMail;

    // Group table config
    protected $groupTable;
    protected $groupName;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param mixed $database
     *
     * @return $this
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param mixed $pdo
     *
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserTable()
    {
        return $this->userTable;
    }

    /**
     * @param mixed $userTable
     *
     * @return $this
     */
    public function setUserTable($userTable)
    {
        $this->userTable = $userTable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserFirstName()
    {
        return $this->userFirstName;
    }

    /**
     * @param mixed $userFirstName
     *
     * @return $this
     */
    public function setUserFirstName($userFirstName)
    {
        $this->userFirstName = $userFirstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserLastName()
    {
        return $this->userLastName;
    }

    /**
     * @param mixed $userLastName
     *
     * @return $this
     */
    public function setUserLastName($userLastName)
    {
        $this->userLastName = $userLastName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserLogin()
    {
        return $this->userLogin;
    }

    /**
     * @param mixed $userLogin
     *
     * @return $this
     */
    public function setUserLogin($userLogin)
    {
        $this->userLogin = $userLogin;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserCode()
    {
        return $this->userCode;
    }

    /**
     * @param mixed $userCode
     *
     * @return $this
     */
    public function setUserCode($userCode)
    {
        $this->userCode = $userCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserMail()
    {
        return $this->userMail;
    }

    /**
     * @param mixed $userMail
     *
     * @return $this
     */
    public function setUserMail($userMail)
    {
        $this->userMail = $userMail;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroupTable()
    {
        return $this->groupTable;
    }

    /**
     * @param mixed $groupTable
     *
     * @return $this
     */
    public function setGroupTable($groupTable)
    {
        $this->groupTable = $groupTable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @param mixed $groupName
     *
     * @return $this
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

    public static function getPdoList()
    {
        return self::PDO_LIST;
    }
}

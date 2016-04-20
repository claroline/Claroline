<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Settings;

class DatabaseSettings extends AbstractValidator
{
    private $driver = 'pdo_mysql';
    private $host = 'localhost';
    private $name = 'claroline';
    private $user = 'root';
    private $password = null;
    private $port = null;
    private $charset = 'UTF8';

    /**
     * @param string $driver
     */
    public function setDriver($driver)
    {
        switch ($driver) {
            case 'PostgreSQL':
                $driver = 'pdo_pgsql';
                break;
            case 'MySQL':
                $driver = 'pdo_mysql';
                break;
        }

        $this->driver = trim($driver);
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = trim($host);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = trim($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = trim($password);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $value = trim($port);
        $this->port = $value === '' ? null : $value;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = trim($user);
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param array $settings
     */
    public function bindData(array $settings)
    {
        foreach ($settings as $name => $value) {
            if (method_exists($this, $method = 'set'.ucfirst($name))) {
                $this->{$method}($value);
            }
        }
    }

    protected function doValidate()
    {
        if ($this->checkIsNotBlank('driver', $this->driver)) {
            $this->checkIsValidDriver('driver', $this->driver);
        }

        $this->checkIsNotBlank('host', $this->host);
        $this->checkIsNotBlank('name', $this->name);
        $this->checkIsNotBlank('user', $this->user);

        if (!empty($this->port)) {
            $this->checkIsPositiveNumber('port', $this->port);
        }
    }
}

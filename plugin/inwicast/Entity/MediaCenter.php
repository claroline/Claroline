<?php

namespace Icap\InwicastBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mediacenter.
 *
 * @ORM\Table(name="inwicast_plugin_mediacenter")
 * @ORM\Entity()
 */
class MediaCenter
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    protected $url = null;

    /**
     * @var string
     *
     * @ORM\Column(name="driver", type="string", length=255)
     */
    protected $driver;

    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=255)
     */
    protected $host;

    /**
     * @var string
     * @ORM\Column(name="port", type="string", length=255)
     */
    protected $port;

    /**
     * @var string
     *
     * @ORM\Column(name="dbname", type="string", length=255)
     */
    protected $dbname;

    /**
     * @var string
     *
     * @ORM\Column(name="user", type="string", length=255)
     */
    protected $user;

    /**
     * @var password
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return MediaCenter
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->url !== null) {
            return preg_replace('~^https?://[^/]+$~', '$0/', $this->url);
        }

        return $this->url;
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
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
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getDbname()
    {
        return $this->dbname;
    }

    /**
     * @param mixed $dbname
     */
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Icap\InwicastBundle\Entity\password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param \Icap\InwicastBundle\Entity\password $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getDatabaseParameters()
    {
        return [
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'dbname' => $this->dbname,
            'user' => $this->user,
            'password' => $this->password,
        ];
    }
}

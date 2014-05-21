<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LdapBundle\Library;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

/**
 * @Service()
 */
class LdapManager
{
    private $yml;
    private $path;
    private $config;
    private $connect;

    /**
     * @InjectParams()
     */
    public function __construct()
    {
        $this->path = __DIR__ . '/../../../../../../app/config/ldap.yml';
        $this->yml = new Parser();
        $this->dumper = new Dumper();
        $this->config = $this->getConfig();
    }

    public function connect()
    {
        $this->connect = ldap_connect($this->getHost(), $this->getPort());

        if ($this->connect) {
            return @ldap_bind($this->connect);
        }
    }

    public function close()
    {
        ldap_close($this->connect);
    }

    public function search($filter, $attributes = array())
    {
        return ldap_search($this->connect, $this->getDn(), $filter, $attributes);
    }

    public function getEntries($search)
    {
        return ldap_get_entries($this->connect, $search);
    }

    public function getHost()
    {
        if (isset($this->config['host'])) {
            return $this->config['host'];
        }

        return '';
    }

    public function getPort()
    {
        if (isset($this->config['port'])) {
            return $this->config['port'];
        }

        return '';
    }

    public function getDn()
    {
        if (isset($this->config['dn'])) {
            return $this->config['dn'];
        }

        return '';
    }

    public function setHost($host)
    {
        $this->config['host'] = $host;
    }

    public function setPort($port)
    {
        $this->config['port'] = $port;
    }

    public function setDn($dn)
    {
        $this->config['dn'] = $dn;
    }

    public function saveConfig()
    {
        return file_put_contents($this->path, $this->dumper->dump($this->config));
    }

    private function getConfig()
    {
        if (!file_exists($this->path)) {
            touch($this->path);
        }

        return $this->yml->parse(file_get_contents($this->path));
    }
}

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
        $this->config = $this->parseYml();
    }

    /**
     * This method create the LDAP link identifier on success and test the connection.
     *
     * @param server An array containing LDAP informations as host, port or dn.
     *
     * @return boolean
     */
    public function connect($server)
    {
        if ($server and isset($server['host'])) {
            if (isset($server['port']) and is_long($server['port'])) {
                $this->connect = ldap_connect($server['host'], $server['port']);
            } else {
                $this->connect = ldap_connect($server['host']);
            }

            if ($this->connect) {
                return @ldap_bind($this->connect);
            }
        }
    }

    /**
     * This method close a previous open connection.
     */
    public function close()
    {
        ldap_close($this->connect);
    }

    /**
     * This method search something in LDAP directory using a filter.
     *
     * @param server An array containing LDAP informations as host, port or dn
     * @param filter Simple or advanced ldap filter
     * @param attributes An array of the required attributes, e.g. array("mail", "sn", "cn").
     *
     * @return Returns a search result identifier or FALSE on error.
     */
    public function search($server, $filter, $attributes = array())
    {
        return ldap_search($this->connect, $server['dn'], $filter, $attributes);
    }

    /**
     * This method reads the entries of a LDAP search.
     *
     * @param search LDAP search result identifier.
     *
     * @return Returns a complete result information in a multi-dimensional array on success and FALSE on error.
     */
    public function getEntries($search)
    {
        return ldap_get_entries($this->connect, $search);
    }

    /**
     * Get a LDAP server configuration by his host name.
     *
     * @param host The host name of the server.
     *
     * @return An array containing LDAP informations as host, port or dn
     */
    public function get($host = null)
    {
        if ($host and isset($this->config['servers'][$host])) {
            return $this->config['servers'][$host];
        }
    }

    /**
     * Test if a given host exist in LDAP configuration.
     *
     * @param host The given host name
     * @param data An array containing LDAP informations as host, port or dn
     *
     * @return boolean
     */
    public function exists($host, $data)
    {
        $servers = isset($this->config['servers']) ? $this->config['servers'] : null;

        if ((!$host or ($host and $host !== $data['host'])) and
            isset($data['host']) and isset($servers[$data['host']])
        ) {
            return true;
        }
    }

    /**
     * Change configuration of automatic user creation when loggin by LDAP
     *
     * @param state A boolean
     *
     * @return boolean
     */
    public function checkUserCreation($state)
    {
        $this->config['userCreation'] = $state;

        return $this->saveConfig();
    }

    /**
     * Delete a server configuration if the newest one replace it.
     *
     * @param host The host name
     * @param data An array containing LDAP informations as host, port or dn
     */
    public function deleteIfReplace($host, $data)
    {
        if ($host and isset($data['host']) and $host !== $data['host']) {
            $this->deleteServer($host);
        }
    }

    /**
     * Delete a server configuration.
     *
     * @param host A host name.
     *
     * @return boolean
     */
    public function deleteServer($host)
    {
        if (isset($this->config['servers']) and isset($this->config['servers'][$host])) {
            unset($this->config['servers'][$host]);

            return $this->saveConfig();
        }
    }

    /**
     * Save the LDAP mapping settings
     *
     * @param host The host name of LDAP server
     * @param settings The settings array containing mapping
     *
     * @return boolean
     */
    public function saveSettings($settings)
    {
        if (isset($settings['host']) and isset($this->config['servers'][$settings['host']])) {
            return $this->saveConfig(array_merge($this->config['servers'][$settings['host']], $settings));
        }
    }

    /**
     * Save the LDAP configuration in a .yml file.
     *
     * @param server An array containing LDAP informations as host, port or dn
     *
     * @return
     */
    public function saveConfig($server = null)
    {
        if (is_array($server) and isset($server['host'])) {
            $this->config['servers'][$server['host']] = $server;
        }

        return file_put_contents($this->path, $this->dumper->dump($this->config));
    }

    /**
     * Return the LDAP configurations.
     *
     * @return Array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Parse .yml file into LDAP configuration array.
     *
     * @return Array
     */
    private function parseYml()
    {
        if (!file_exists($this->path)) {
            touch($this->path);
        }

        return $this->yml->parse(file_get_contents($this->path));
    }
}

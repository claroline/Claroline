<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LdapBundle\Manager;

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

    public function __construct()
    {
        $this->path = __DIR__.'/../../../../../../app/config/Authentication/claroline.ldap.yml';
        $this->yml = new Parser();
        $this->dumper = new Dumper();
        $this->config = $this->parseYml();
    }

    /**
     * This method create the LDAP link identifier on success and test the connection.
     *
     * @param server   An array containing LDAP informations as host, port or dn.
     *
     * @return bool
     */
    public function connect($server, $user = null, $password = null)
    {
        if ($server && isset($server['host'])) {
            if (isset($server['port']) && is_numeric($server['port'])) {
                $this->connect = ldap_connect($server['host'], $server['port']);
            } else {
                $this->connect = ldap_connect($server['host']);
            }

            ldap_set_option($this->connect, LDAP_OPT_PROTOCOL_VERSION, $server['protocol_version']);

            if ($this->connect && $user && $password) {
                return ldap_bind($this->connect, $user, $password);
            } elseif ($this->connect) {
                return ldap_bind($this->connect);
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

    public function findUser($server, $user)
    {
        $server = $this->get($server);
        $filter = '(&(objectClass='.$server['objectClass'].'))';
        $search = ldap_search(
            $this->connect,
            $this->prepareUsername($server, $user),
            $filter,
            array(
                $server['userName'],
                $server['firstName'],
                $server['lastName'],
                $server['email'],
                'userpassword',
            )
        );

        $entries = $this->getEntries($search);
        $user = array();

        foreach ($entries as $entry) {
            if ($entry) {
                $user['username'] = $entry[$server['userName']][0];
                $user['first_name'] = $entry[$server['firstName']][0];
                $user['last_name'] = $entry[$server['lastName']][0];
                $user['email'] = $entry[$server['email']][0];
                $user['password'] = $entry['userpassword'][0];
            }
        }

        return $user;
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
     * Get a LDAP server configuration by his name.
     *
     * @param name The name of server.
     *
     * @return An array containing LDAP informations as host, port or dn
     */
    public function get($name = null)
    {
        if ($name and isset($this->config['servers'][$name])) {
            return $this->config['servers'][$name];
        }
    }

    /**
     * Test if a given server name exist in LDAP configuration.
     *
     * @param name The name of the server
     * @param data An array containing LDAP informations as host, port or dn
     *
     * @return bool
     */
    public function exists($name, $data)
    {
        $servers = isset($this->config['servers']) ? $this->config['servers'] : null;

        if ((!$name or ($name and $name !== $data['name'])) and
            isset($data['name']) and isset($servers[$data['name']])
        ) {
            return true;
        }
    }

    /**
     * Delete a server configuration if the newest one replace it.
     *
     * @param name The name of the server
     * @param data An array containing LDAP informations as host, port or dn
     */
    public function deleteIfReplace($name, $data)
    {
        if ($name and isset($data['name']) and $name !== $data['name']) {
            $this->deleteServer($name);
        }
    }

    /**
     * Delete a server configuration.
     *
     * @param name The name of the server.
     *
     * @return bool
     */
    public function deleteServer($name)
    {
        if (isset($this->config['servers']) and isset($this->config['servers'][$name])) {
            unset($this->config['servers'][$name]);

            return $this->saveConfig();
        }
    }

    /**
     * Save the LDAP mapping settings.
     *
     * @param settings The settings array containing mapping
     *
     * @return bool
     */
    public function saveSettings($settings)
    {
        if (isset($settings['name']) and isset($this->config['servers'][$settings['name']])) {
            return $this->saveConfig(array_merge($this->config['servers'][$settings['name']], $settings));
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
        if (is_array($server) and isset($server['name'])) {
            $this->config['servers'][$server['name']] = $server;
        }

        return file_put_contents($this->path, $this->dumper->dump($this->config, 3));
    }

    /**
     * Get LDAP opbject classes.
     *
     * @param server An array containing LDAP informations as host, port or dn
     */
    public function getClasses($server)
    {
        $classes = array();

        if ($search = $this->search($server, '(&(objectClass=*))', array('objectclass'))) {
            $entries = $this->getEntries($search);
            foreach ($entries as $objectClass) {
                if (isset($objectClass['objectclass'])) {
                    unset($objectClass['objectclass']['count']);
                    $classes = array_merge($classes, $objectClass['objectclass']);
                }
            }
        }

        return array_unique($classes);
    }

    /**
     * Get list of LDAP users.
     *
     * @param server An array containing LDAP informations as host, port or dn
     */
    public function getUsers($server)
    {
        $users = array();

        if (isset($server['objectClass']) and
            $search = $this->search($server, '(&(objectClass='.$server['objectClass'].'))')
        ) {
            $users = $this->getEntries($search);
        }

        return $users;
    }

    /**
     * Get list of LDAP users.
     *
     * @param server An array containing LDAP informations as host, port or dn
     */
    public function getGroups($server)
    {
        $groups = array();

        if (isset($server['group']) and
            $search = $this->search($server, '(&(objectClass='.$server['group'].'))')
        ) {
            $groups = $this->getEntries($search);
        }

        return $groups;
    }

    /**
     * Return the LDAP configurations.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Return a list of available servers.
     */
    public function getServers()
    {
        $servers = array();

        if (isset($this->config['servers']) and is_array($this->config['servers'])) {
            foreach ($this->config['servers'] as $server) {
                $servers[] = $server['name'];
            }
        }

        return $servers;
    }

    /**
     * Check if the users settings (mapping) are defined.
     */
    public function userMapping($server)
    {
        foreach (['userName', 'firstName', 'lastName', 'email', 'password'] as $field) {
            if (!(isset($server[$field]) and $server[$field] !== '')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse .yml file into LDAP configuration array.
     *
     * @return array
     */
    private function parseYml()
    {
        if (!file_exists($this->path)) {
            touch($this->path);
        }

        return $this->yml->parse(file_get_contents($this->path));
    }

    /**
     * Authenticate ldap user.
     *
     * @param name The name of the server.
     *
     * @return bool
     */
    public function authenticate($name, $user, $password)
    {
        $server = $this->get($name);

        if ($this->connect($server, $this->prepareUsername($server, $user), $password)) {
            return true;
        }
    }

    private function prepareUsername($server, $user)
    {
        if ($server['append_dn']) {
            return $server['userName'].'='.$user.','.$server['dn'];
        }

        return $user;
    }
}

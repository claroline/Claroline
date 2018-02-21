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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Claroline\CoreBundle\Manager\RegistrationManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\LdapBundle\Entity\LdapUser;
use Claroline\LdapBundle\Exception\InvalidLdapCredentialsException;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

/**
 * @Service()
 */
class LdapManager
{
    /** @var Parser */
    private $yml;
    /** @var string */
    private $path;
    /** @var array */
    private $config;
    /** @var mixed */
    private $connect;
    /** @var ObjectManager */
    private $om;
    /** @var RegistrationManager */
    private $registrationManager;
    /** @var UserManager */
    private $userManager;
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;
    /** @var \Claroline\LdapBundle\Repository\LdapUserRepository */
    private $ldapUserRepo;
    /** @var TranslatorInterface */
    private $translator;
    /** @var Authenticator */
    private $authenticator;

    /**
     * @InjectParams({
     *     "authenticationDir"      = @Inject("%claroline.param.authentication_directory%"),
     *     "om"                     = @Inject("claroline.persistence.object_manager"),
     *     "registrationManager"    = @Inject("claroline.manager.registration_manager"),
     *     "userManager"            = @Inject("claroline.manager.user_manager"),
     *     "platformConfigHandler"  = @Inject("claroline.config.platform_config_handler"),
     *     "translator"             = @Inject("translator"),
     *     "authenticator"          = @Inject("claroline.authenticator")
     * })
     */
    public function __construct(
        $authenticationDir,
        ObjectManager $om,
        RegistrationManager $registrationManager,
        UserManager $userManager,
        PlatformConfigurationHandler $platformConfigHandler,
        TranslatorInterface $translator,
        Authenticator $authenticator
    ) {
        $this->path = $authenticationDir.'claroline.ldap.yml';
        $this->yml = new Parser();
        $this->dumper = new Dumper();
        $this->config = $this->parseYml();
        $this->om = $om;
        $this->registrationManager = $registrationManager;
        $this->userManager = $userManager;
        $this->ldapUserRepo = $om->getRepository('ClarolineLdapBundle:LdapUser');
        $this->platformConfigHandler = $platformConfigHandler;
        $this->translator = $translator;
        $this->authenticator = $authenticator;
    }

    /**
     * This method create the LDAP link identifier on success and test the connection.
     *
     * @param server   An array containing LDAP informations as host, port or dn
     *
     * @return bool
     */
    public function connect($server, $user = null, $password = null, $isAuthentication = false)
    {
        if ($server && isset($server['host'])) {
            if (isset($server['port']) && is_numeric($server['port'])) {
                $this->connect = ldap_connect($server['host'], $server['port']);
            } else {
                $this->connect = ldap_connect($server['host']);
            }

            ldap_set_option($this->connect, LDAP_OPT_PROTOCOL_VERSION, $server['protocol_version']);

            try {
                if ($isAuthentication && $this->connect && $user && $password) {
                    $user = $this->findDnFromUserName($server, $user);

                    return (false === $user) ? $user : ldap_bind($this->connect, $user, $password);
                } elseif ($this->connect) {
                    return ldap_bind($this->connect);
                }
            } catch (\Exception $e) {
                throw new InvalidLdapCredentialsException();
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

    public function findUserAsClaroUser($server, $userName)
    {
        $user = $this->findUser($server, $userName);
        $claroUser = new User();
        if (!empty($user)) {
            $claroUser->setUsername($userName);
            $claroUser->setFirstName($user['first_name']);
            $claroUser->setLastName($user['last_name']);
            $claroUser->setEmail($user['email']);
            $claroUser->setPassword($user['password']);
        }

        return $claroUser;
    }

    public function findUser($server, $user)
    {
        $server = $this->get($server);
        $this->connect($server);
        $filter = '(&(objectClass='.$server['objectClass'].'))';
        if (false === ($user = $this->findDnFromUserName($server, $user))) {
            return [];
        }
        $search = ldap_search(
            $this->connect,
            $this->prepareUsername($server, $user),
            $filter,
            [
                $server['userName'],
                $server['firstName'],
                $server['lastName'],
                $server['email'],
            ]
        );

        $entries = $this->getEntries($search);
        $user = [];

        foreach ($entries as $entry) {
            if ($entry) {
                $user['username'] = (isset($entry[$server['userName']])) ? $entry[$server['userName']][0] : '';
                $user['first_name'] = (isset($entry[$server['firstName']])) ? $entry[$server['firstName']][0] : '';
                $user['last_name'] = (isset($entry[$server['lastName']])) ? $entry[$server['lastName']][0] : '';
                $user['email'] = (isset($entry[$server['email']])) ? $entry[$server['email']][0] : '';
                $user['password'] = (isset($entry['userpassword'])) ? $entry['userpassword'][0] : '';
            }
        }

        return $user;
    }

    /**
     * This method search something in LDAP directory using a filter.
     *
     * @param server An array containing LDAP informations as host, port or dn
     * @param filter Simple or advanced ldap filter
     * @param attributes An array of the required attributes, e.g. array("email", "sn", "cn")
     *
     * @return Returns a search result identifier or FALSE on error
     */
    public function search($server, $filter, $attributes = [])
    {
        return ldap_search($this->connect, $server['dn'], $filter, $attributes);
    }

    /**
     * This method reads the entries of a LDAP search.
     *
     * @param search LDAP search result identifier
     *
     * @return Returns a complete result information in a multi-dimensional array on success and FALSE on error
     */
    public function getEntries($search)
    {
        $entries = ldap_get_entries($this->connect, $search);

        array_walk_recursive($entries, function (&$item) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });

        return $entries;
    }

    /**
     * Get a LDAP server configuration by his name.
     *
     * @param name The name of server
     *
     * @return An array containing LDAP informations as host, port or dn
     */
    public function get($name = null)
    {
        if ($name && isset($this->config['servers'][$name])) {
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

        if ((!$name || ($name && $name !== $data['name'])) &&
            isset($data['name']) && isset($servers[$data['name']])
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
        if ($name && isset($data['name']) && $name !== $data['name']) {
            $this->ldapUserRepo->updateUsersByServerName($name, $data['name']);
            $this->deleteServer($name);
        }
    }

    /**
     * Delete a server configuration.
     *
     * @param $name The name of the server
     * @param name The name of the server
     *
     * @return bool
     */
    public function deleteServer($name)
    {
        if (isset($this->config['servers']) && isset($this->config['servers'][$name])) {
            unset($this->config['servers'][$name]);
            $this->ldapUserRepo->deleteUsersByServerName($name);

            return $this->saveConfig();
        }
    }

    /**
     * Save the LDAP mapping settings.
     *
     * @param $settings The settings array containing mapping
     *
     * @return bool
     */
    public function saveSettings($settings)
    {
        if (isset($settings['name']) && isset($this->config['servers'][$settings['name']])) {
            return $this->saveConfig(array_merge($this->config['servers'][$settings['name']], $settings));
        }
    }

    /**
     * Save the LDAP configuration in a .yml file.
     *
     * @param $server An array containing LDAP informations as host, port or dn
     *
     * @return mixed
     */
    public function saveConfig($server = null)
    {
        if (is_array($server) && isset($server['name'])) {
            if (!isset($server['objectClass'])) {
                $server = $this->setDefaultServerUserObject($server);
            }
            $this->config['servers'][$server['name']] = $server;
        }

        return file_put_contents($this->path, $this->dumper->dump($this->config, 3));
    }

    /**
     * Get LDAP opbject classes.
     *
     * @param $server An array containing LDAP informations as host, port or dn
     *
     * @return array
     */
    public function getClasses($server)
    {
        $classes = [];

        if ($search = $this->search($server, '(&(objectClass=*))', ['objectclass'])) {
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
        $users = [];

        if (isset($server['objectClass']) &&
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
        $groups = [];

        if (isset($server['group']) &&
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
        $servers = [];

        if (isset($this->config['servers']) && is_array($this->config['servers'])) {
            foreach ($this->config['servers'] as $server) {
                $servers[] = $server['name'];
            }
        }

        return $servers;
    }

    /**
     * Return a list of active servers.
     *
     * @return array
     */
    public function getActiveServers()
    {
        $activeServers = [];

        if (isset($this->config['servers']) && is_array($this->config['servers'])) {
            foreach ($this->config['servers'] as $server) {
                if (isset($server['active']) && isset($server['objectClass']) && $server['active']) {
                    $activeServers[] = $server['name'];
                }
            }
        }

        return $activeServers;
    }

    /**
     * @param $user
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getRegistrationForm($user)
    {
        return $this->registrationManager->getRegistrationForm($user);
    }

    /**
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createNewAccount(Request $request, $ldapLogin, $server)
    {
        $user = new User();
        $form = $this->getRegistrationForm($user);
        $form->handleRequest($request);
        $session = $request->getSession();
        if ($form->isValid()) {
            $user = $this->userManager->createUser($user);
            $this->createLdapUser($server, $ldapLogin, $user);

            $msg = $this->translator->trans('account_created', [], 'platform');
            $session->getFlashBag()->add('success', $msg);

            if ($this->platformConfigHandler->getParameter('registration_mail_validation')) {
                $msg = $this->translator->trans('please_validate_your_account', [], 'platform');
                $session->getFlashBag()->add('success', $msg);
            }

            return $this->registrationManager->loginUser($user, $request);
        }

        return ['form' => $form->createView(), 'serverName' => $server];
    }

    /**
     * @param Request $request
     * @param $ldapLogin
     * @param $server
     * @param null $username
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function linkAccount(Request $request, $ldapLogin, $server, $username = null)
    {
        $verifyPassword = false;
        $password = null;
        if (null === $username) {
            $verifyPassword = true;
            $username = $request->get('_username');
            $password = $request->get('_password');
        }
        $isAuthenticated = $this->authenticator->authenticate($username, $password, $verifyPassword);
        if ($isAuthenticated) {
            $user = $this->userManager->getUserByUsername($username);
            $this->createLdapUser($server, $ldapLogin, $user);

            return $this->registrationManager->loginUser($user, $request);
        } else {
            return ['error' => 'login_error'];
        }
    }

    public function unlinkAccount($userId)
    {
        $this->ldapUserRepo->unlinkLdapUser($userId);
    }

    /**
     * Check if the users settings (mapping) are defined.
     */
    public function userMapping($server)
    {
        foreach (['userName', 'firstName', 'lastName', 'email'] as $field) {
            if (!(isset($server[$field]) && '' !== $server[$field])) {
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
     * @param name The name of the server
     *
     * @return bool
     */
    public function authenticate(Request $request, $serverName)
    {
        $server = $this->get($serverName);
        $username = $request->get('_username');
        $password = $request->get('_password');
        if (
            !empty($username) &&
            !empty($password) &&
            $this->connect($server, $this->prepareUsername($server, $username), $password, true)
        ) {
            $ldapUser = $this->ldapUserRepo->findOneBy(['serverName' => $server, 'ldapId' => $username]);
            if (null !== $ldapUser) {
                return $this->registrationManager->loginUser($ldapUser->getUser(), $request);
            }
            if ($this->platformConfigHandler->getParameter('direct_third_party_authentication')) {
                $user = $this->userManager->getUserByUsername($username);
                if (null === $user) {
                    throw $this->getUsernameNotFoundException($username);
                }
                $this->createLdapUser($serverName, $username, $user);

                return $this->registrationManager->loginUser($user);
            }

            throw $this->getUsernameNotFoundException($username);
        }

        throw new InvalidLdapCredentialsException();
    }

    private function prepareUsername($server, $user)
    {
        if ($server['append_dn']) {
            return $server['userName'].'='.$user.','.$server['dn'];
        }

        if ($server['append_cn']) {
            return 'CN='.$user.','.$server['dn'];
        }

        return $user;
    }

    private function createLdapUser($serverName, $ldapUsername, User $user)
    {
        $ldapUser = new LdapUser($serverName, $ldapUsername, $user);
        $this->om->persist($ldapUser);
        $this->om->flush();

        return $ldapUser;
    }

    private function getUsernameNotFoundException($username)
    {
        $exp = new UsernameNotFoundException();
        $exp->setUsername($username);

        return $exp;
    }

    private function findDnFromUserName($server, $user)
    {
        if (isset($server['append_dn']) && !$server['append_dn'] && isset($server['userName'])) {
            $ldap_cursor = ldap_search($this->connect, $server['dn'], $server['userName'].'='.$user);
            if (false === $ldap_cursor) {
                ldap_set_option($this->connect, LDAP_OPT_REFERRALS, 0);
                $ldap_cursor = ldap_search($this->connect, $server['dn'], $server['userName'].'='.$user);
            }
            if (false === $ldap_cursor) {
                return false;
            }
            $ret = ldap_first_entry($this->connect, $ldap_cursor);
            if (false === $ret) {
                return false;
            }
            $user = ldap_get_dn($this->connect, $ret);
            ldap_free_result($ldap_cursor);
        }

        return $user;
    }

    private function setDefaultServerUserObject($server)
    {
        $server['objectClass'] = 'inetOrgPerson';
        $server['userName'] = 'uid';
        $server['firstName'] = 'givenname';
        $server['lastName'] = 'displayname';
        $server['email'] = 'email';
        $server['code'] = '';
        $server['locale'] = '';

        return $server;
    }
}

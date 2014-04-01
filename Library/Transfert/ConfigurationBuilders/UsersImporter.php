<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\Processor;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Transfert\Merger;

/**
 * @DI\Service("claroline.importer.users_importer")
 * @DI\Tag("claroline.importer")
 */
class UsersImporter extends Importer implements ConfigurationInterface
{
    private static $data;
    private $om;

    /**
     * @DI\InjectParams({
     *     "om"      = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('users');
        $this->addUsersSection($rootNode);

        return $treeBuilder;
    }

    //@todo include owner in the verification if this section exists
    public function addUsersSection($rootNode)
    {
         $usernames = array();

        foreach($this->om->getRepository('Claroline\CoreBundle\Entity\User')->findUsernames() as $username)
        {
            $usernames[] = $username['username'];
        }

        $emails = array();

        foreach($this->om->getRepository('Claroline\CoreBundle\Entity\User')->findEmails() as $mail)
        {
            $emails[] = $mail['mail'];
        }

        $codes = array();

        foreach($this->om->getRepository('Claroline\CoreBundle\Entity\User')->findCodes() as $code)
        {
            $codes[] = $code['code'];
        }


        $configuration = $this->getConfiguration();
        $availableRoleName = array();

        if (isset($configuration['roles'])) {
            foreach ($configuration['roles'] as $role) {
                $availableRoleName[] = $role['role']['name'];
            }
        }

        $owner = null;

       if (isset($configuration['members']['owner']['username'])) {
            $owner = $configuration['members']['owner']['username'];
       }

        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('user')
                        ->children()
                            ->scalarNode('first_name')->example('Jane')->isRequired()->end()
                            ->scalarNode('last_name')->example('Doe')->isRequired()->end()
                            ->scalarNode('username')->example('janedoe')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($usernames) {
                                            return call_user_func_array(
                                                __CLASS__ . '::usernameAlreadyExistsInDatabase',
                                                array($v, $usernames)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The username %s already exists in the database")
                                ->end()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($usernames) {
                                            return call_user_func_array(
                                                __CLASS__ . '::usernameAlreadyExistsInConfig',
                                                array($v, $usernames)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The username %s already exists in the configuration")
                                ->end()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($owner) {
                                            return call_user_func_array(
                                                __CLASS__ . '::ownerAlreadyExists',
                                                array($v, $owner)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The owner %s already exists in the configuration")
                                ->end()
                            ->end()
                            ->scalarNode('password')->example('noidea')->isRequired()->end()
                            ->scalarNode('mail')->example('jdoe@gmail.com')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($emails) {
                                            return call_user_func_array(
                                                __CLASS__ . '::emailAlreadyExistsInDatabase',
                                                array($v, $emails)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The email %s already exists")
                                ->end()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($emails) {
                                            return call_user_func_array(
                                                __CLASS__ . '::emailsAlreadyExistsInConfig',
                                                array($v, $emails)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The email %s already exists in the configuration")
                                ->end()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($emails) {
                                            return call_user_func_array(
                                                __CLASS__ . '::emailIsValid',
                                                array($v, $emails)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The email %s is invalid")
                                ->end()
                            ->end()
                            ->scalarNode('code')->example('usr#1234569789')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($codes) {
                                            return call_user_func_array(
                                                __CLASS__ . '::codeAlreadyExistsInDatabase',
                                                array($v, $codes)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The code %s already exists")
                                ->end()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($codes) {
                                            return call_user_func_array(
                                                __CLASS__ . '::codeAlreadyExistsInConfig',
                                                array($v, $codes)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The code %s already exists")
                                ->end()
                            ->end()
                            ->arrayNode('roles')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->example('collaborator')->isRequired()
                                        ->validate()
                                            ->ifTrue(
                                                function ($v) use ($availableRoleName) {
                                                    return call_user_func_array(
                                                        __CLASS__ . '::roleNameExists',
                                                        array($v, $availableRoleName)
                                                    );
                                                }
                                            )
                                            ->thenInvalid("The role name %s doesn't exists")
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function getName()
    {
        return 'user';
    }

    /**
     * Validate the workspace properties.
     *
     * @todo show the expected array
     * @param array $data
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        self::setData($data);
        $configuration = $processor->processConfiguration($this, $data);
    }

    public static function usernameAlreadyExistsInDatabase($v, $usernames)
    {
        return in_array($v, $usernames);
    }

    public static function usernameAlreadyExistsInConfig($v)
    {
        $users = self::getData();

        $found = false;

        foreach ($users as $el) {
            foreach ($el as $user) {
                if ($user['user']['username'] === $v) {
                    if ($found) {
                        return true;
                    }
                    $found = true;
                }
            }
        }

        return false;
    }

    public static function emailsAlreadyExistsInConfig($v)
    {
        $users = self::getData();
        $found = false;

        foreach ($users as $el) {
            foreach ($el as $user) {
                if ($user['user']['mail'] === $v) {
                    if ($found) {
                        return true;
                    }
                    $found = true;
                }
            }
        }

        return false;
    }

    public static function codeAlreadyExistsInConfig($v)
    {
        $users = self::getData();
        $found = false;

        foreach ($users as $el) {
            foreach ($el as $user) {
                if ($user['user']['code'] === $v) {
                    if ($found) {
                        return true;
                    }
                    $found = true;
                }
            }
        }

        return false;
    }

    public static function emailAlreadyExistsInDatabase($v, $mails)
    {
        return in_array($v, $mails);
    }

    public static function emailIsValid($v)
    {
        return !filter_var($v, FILTER_VALIDATE_EMAIL);
    }

    public static function codeAlreadyExistsInDatabase($v, $code)
    {
        return in_array($v, $code);
    }

    private static function setData($data)
    {
        self::$data = $data;
    }

    private static function getData()
    {
        return self::$data;
    }

    public static function roleNameExists($v, $roles)
    {
        return !in_array($v, $roles);
    }

    public static function ownerAlreadyExists($v, $owner)
    {
        return $owner === $v ? true: false;
    }
}
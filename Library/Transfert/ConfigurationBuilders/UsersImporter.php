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

//@todo exception content

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

        $mergedRoles = array();
        // get merged roles

        $availableRoleName = array();

        foreach ($mergedRoles as $el) {
            foreach ($el as $role) {
                $availableRoleName[] = $role['role']['name'];
            }
        }

        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('user')
                        ->children()
                            ->scalarNode('first_name')->isRequired()->end()
                            ->scalarNode('last_name')->isRequired()->end()
                            ->scalarNode('username')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($usernames) {
                                            return call_user_func_array(
                                                __CLASS__ . '::usernameAlreadyExistsInDatabase',
                                                array($v, $usernames)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The username w/e already exists in the database")
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
                                    ->thenInvalid("The username w/e already exists in the configuration")
                                ->end()
                            ->end()
                            ->scalarNode('password')->isRequired()->end()
                            ->scalarNode('mail')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($emails) {
                                            return call_user_func_array(
                                                __CLASS__ . '::emailAlreadyExistsInDatabase',
                                                array($v, $emails)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The email w/e already exists")
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
                                    ->thenInvalid("The email w/e already exists in the configuration")
                                ->end()
                            ->end()
                            ->scalarNode('code')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($codes) {
                                            return call_user_func_array(
                                                __CLASS__ . '::codeAlreadyExistsInDatabase',
                                                array($v, $codes)
                                            );
                                        }
                                    )
                                    ->thenInvalid("The code w/e already exists")
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
                                    ->thenInvalid("The code w/e already exists")
                                ->end()
                            ->end()
                            ->arrayNode('roles')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->isRequired()
                                        ->validate()
                                            ->ifTrue(
                                                function ($v) use ($availableRoleName) {
                                                    return call_user_func_array(
                                                        __CLASS__ . '::roleNameExists',
                                                        array($v, $availableRoleName)
                                                    );
                                                }
                                            )
                                            ->thenInvalid("The role name w/e doesn't exists")
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
        return 'user_importer';
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
}
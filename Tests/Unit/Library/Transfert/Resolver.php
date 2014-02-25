<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Transfert\Merger;

class MergerTest extends MockeryTestCase
{
    private $path;
    private $merger;

    public function __construct()
    {
        $this->path = __DIR__ . '/../../../Stub/transfert/valid/full';
        $this->merger = new Merger();
    }

    public function testMergeUserConfigurations()
    {
        $result = $this->merger->mergeUserConfigurations($this->path);

        //generated with var_export()
        $expected = array(
            'users' =>
                array(
                    0 =>
                        array(
                            'user' =>
                                array(
                                    'first_name' => 'ffdgdf',
                                    'last_name' => 'sdfsd',
                                    'username' => 'user1',
                                    'password' => 'enclair',
                                    'mail' => 'mail1@gmail.com',
                                    'code' => 'USER01',
                                    'roles' =>
                                        array(
                                            0 =>
                                                array(
                                                    'name' => 'ROLE_1',
                                                ),
                                            1 =>
                                                array(
                                                    'name' => 'ROLE_2',
                                                ),
                                            2 =>
                                                array(
                                                    'name' => 'ROLE_3',
                                                ),
                                        ),
                                ),
                        ),
                    1 =>
                        array(
                            'user' =>
                                array(
                                    'first_name' => 'd',
                                    'last_name' => 'ddsd',
                                    'username' => 'user1',
                                    'password' => 'enclair',
                                    'mail' => 'mail2@gmail.com',
                                    'code' => 'USER02',
                                ),
                        ),
                    2 =>
                        array(
                            'user' =>
                                array(
                                    'first_name' => 'aaaa',
                                    'last_name' => 'aaaa',
                                    'username' => 'user1',
                                    'password' => 'aaaa',
                                    'mail' => 'mail1@gmail.com',
                                    'code' => 'USER01',
                                ),
                        ),
                    3 =>
                        array(
                            'user' =>
                                array(
                                    'first_name' => 'bbbb',
                                    'last_name' => 'bbbb',
                                    'username' => 'user2',
                                    'password' => 'bbbb',
                                    'mail' => 'mail2@gmail.com',
                                    'code' => 'USER02',
                                ),
                        ),
                    4 =>
                        array(
                            'user' =>
                                array(
                                    'first_name' => 'bbbb',
                                    'last_name' => 'bbbb',
                                    'username' => 'user3',
                                    'password' => 'bbbb',
                                    'mail' => 'mail3@gmail.com',
                                    'code' => 'USER03',
                                ),
                        ),
                ),
        );

        $this->assertEquals($result, $expected);
    }

    public function testMergeGroupConfigurations()
    {
        $result = $this->merger->mergeGroupConfigurations($this->path);

        //generated with var_export()
        $expected = array(
            'groups' =>
                array(
                    0 =>
                        array(
                            'group' =>
                                array(
                                    'name' => 'blabla',
                                    'users' =>
                                        array(
                                            0 =>
                                                array(
                                                    'username' => 'user1',
                                                ),
                                            1 =>
                                                array(
                                                    'username' => 'user2',
                                                ),
                                            2 =>
                                                array(
                                                    'username' => 'user3',
                                                ),
                                        ),
                                    'roles' =>
                                        array(
                                            0 =>
                                                array(
                                                    'name' => 'ROLE_1',
                                                ),
                                            1 =>
                                                array(
                                                    'name' => 'ROLE_2',
                                                ),
                                            2 =>
                                                array(
                                                    'name' => 'ROLE_3',
                                                ),
                                        ),
                                ),
                        ),
                    1 =>
                        array(
                            'group' =>
                                array(
                                    'name' => 'name1',
                                    'users' =>
                                        array(
                                            0 =>
                                                array(
                                                    'username' => 'user1',
                                                ),
                                            1 =>
                                                array(
                                                    'username' => 'user2',
                                                ),
                                            2 =>
                                                array(
                                                    'username' => 'user3',
                                                ),
                                        ),
                                ),
                        ),
                    2 =>
                        array(
                            'group' =>
                                array(
                                    'name' => 'name2',
                                    'users' =>
                                        array(
                                            0 =>
                                                array(
                                                    'username' => 'user1',
                                                ),
                                            1 =>
                                                array(
                                                    'username' => 'user2',
                                                ),
                                            2 =>
                                                array(
                                                    'username' => 'user3',
                                                ),
                                        ),
                                    'roles' =>
                                        array(
                                            0 =>
                                                array(
                                                    'name' => 'mergedrole',
                                                ),
                                        ),
                                ),
                        ),
                ),
        );

        $this->assertEquals($expected, $result);
    }

    public function testMergeRoleConfigurations()
    {
        $result = $this->merger->mergeRoleConfigurations($this->path);

        $expected = array(
            'roles' =>
                array(
                    0 =>
                        array(
                            'role' =>
                                array(
                                    'name' => 'role1',
                                    'translation' => 'totottoo',
                                    'is_base_role' => true,
                                ),
                        ),
                    1 =>
                        array(
                            'role' =>
                                array(
                                    'name' => 'role1',
                                    'translation' => 'totottoo',
                                    'is_base_role' => false,
                                ),
                        ),
                    2 =>
                        array(
                            'role' =>
                                array(
                                    'name' => 'role1',
                                    'translation' => 'totottoo',
                                    'is_base_role' => true,
                                ),
                        ),
                    3 =>
                        array(
                            'role' =>
                                array(
                                    'name' => 'role2',
                                    'translation' => 'totottoo',
                                    'is_base_role' => false,
                                ),
                        ),
                ),
        );

        $this->assertEquals($expected, $result);
    }

    public function testMergeToolConfigurations()
    {
        $result = $this->merger->mergeToolConfigurations($this->path);

        $expected = array(
            'tools' =>
                array(
                    0 =>
                        array(
                            'tool' =>
                                array(
                                    'name' => 'home',
                                    'translation' => 'accueil',
                                    'config' => 'tools/home.yml',
                                    'roles' =>
                                        array(
                                            0 =>
                                                array(
                                                    'name' => 'anonymous',
                                                ),
                                            1 =>
                                                array(
                                                    'name' => 'visitor',
                                                ),
                                            2 =>
                                                array(
                                                    'name' => 'collaborator',
                                                ),
                                            3 =>
                                                array(
                                                    'name' => 'manager',
                                                ),
                                        ),
                                ),
                        ),
                    1 =>
                        array(
                            'tool' =>
                                array(
                                    'name' => 'agenda',
                                    'roles' =>
                                        array(
                                            0 =>
                                                array(
                                                    'name' => 'visitor',
                                                ),
                                        ),
                                    'translation' => 'agenda',
                                ),
                        ),
                    2 =>
                        array(
                            'tool' =>
                                array(
                                    'name' => 'badges',
                                ),
                        ),
                    3 =>
                        array(
                            'tool' =>
                                array(
                                    'name' => 'resource_manager',
                                    'config' => 'tools/resource_manager.yml',
                                ),
                        ),
                    4 =>
                        array(
                            'tool' =>
                                array(
                                    'name' => 'home',
                                    'translation' => 'accueil',
                                    'config' => 'tools/home.yml',
                                    'data' =>
                                        array(
                                            'tabs' =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'tab' =>
                                                                array(
                                                                    'name' => 'tabname',
                                                                    'widgets' =>
                                                                        array(
                                                                            0 =>
                                                                                array(
                                                                                    'widget' =>
                                                                                        array(
                                                                                            'name' => 'text',
                                                                                            'type' => 'text',
                                                                                            'config' => 'tools/widget/text01.yml',
                                                                                        ),
                                                                                ),
                                                                            1 =>
                                                                                array(
                                                                                    'widget' =>
                                                                                        array(
                                                                                            'name' => 'rss',
                                                                                            'type' => 'rss',
                                                                                            'config' => 'tools/widget/rss01.yml',
                                                                                        ),
                                                                                ),
                                                                        ),
                                                                ),
                                                        ),
                                                    1 =>
                                                        array(
                                                            'tab' =>
                                                                array(
                                                                    'name' => 'tabname02',
                                                                ),
                                                        ),
                                                ),
                                        ),
                                    'roles' =>
                                        array(
                                            0 =>
                                                array(
                                                    'name' => 'anonymous',
                                                ),
                                            1 =>
                                                array(
                                                    'name' => 'visitor',
                                                ),
                                            2 =>
                                                array(
                                                    'name' => 'collaborator',
                                                ),
                                            3 =>
                                                array(
                                                    'name' => 'manager',
                                                ),
                                        ),
                                ),
                        ),
                    5 =>
                        array(
                            'tool' =>
                                array(
                                    'name' => 'agenda',
                                    'roles' =>
                                        array(
                                            0 =>
                                                array(
                                                    'name' => 'visitor',
                                                ),
                                        ),
                                    'translation' => 'agenda',
                                ),
                        ),
                    6 =>
                        array(
                            'tool' =>
                                array(
                                    'name' => 'badges',
                                ),
                        ),
                    7 =>
                        array(
                            'tool' =>
                                array(
                                    'name' => 'resource_manager',
                                    'config' => 'tools/resource_manager.yml',
                                ),
                        ),
                ),
        );

        $this->assertEquals($expected, $result);
    }
} 
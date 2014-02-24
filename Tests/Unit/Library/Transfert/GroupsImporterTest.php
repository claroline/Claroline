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
use Mockery as m;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\GroupsImporter;
use Symfony\Component\Yaml\Yaml;

class GroupsImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->merger = $this->mock('Claroline\CoreBundle\Library\Transfert\Merger');
        $this->importer = new GroupsImporter($this->om, $this->merger);
    }

    /**
     *  @dataProvider validateProvider
     */
    public function testValidate($path, $isExceptionExpected, $databaseUsernames, $names)
    {
        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $groupRepo = $this->mock('Claroline\CoreBundle\Repository\GroupRepository');
        $this->om->shouldReceive('getRepository')->with('Claroline\CoreBundle\Entity\Group')->andReturn($groupRepo);
        $groupRepo->shouldReceive('findNames')->andReturn($names);

        $userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->om->shouldReceive('getRepository')->with('Claroline\CoreBundle\Entity\User')->andReturn($userRepo);
        $userRepo->shouldReceive('findUsernames')->andReturn($databaseUsernames);
        $this->merger->shouldReceive('mergeUserConfigurations')->andReturn($this->getMergedUsers());
        $this->merger->shouldReceive('mergeRoleConfigurations')->andReturn($this->getMergedRoles());

        $data = Yaml::parse(file_get_contents($path));
        $roles['groups'] = $data['groups'];
        $this->importer->validate($roles);
    }

    public function validateProvider()
    {
        return array(
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/groups01.yml',
                'isExceptionExpected' => false,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/groups/existing_name.yml',
                'isExceptionExpected' => true,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/groups01.yml',
                'isExceptionExpected' => true,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array('name1')
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/groups01.yml',
                'isExceptionExpected' => true,
                'databaseUsernames' => array(array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/groups01.yml',
                'isExceptionExpected' => false,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/groups/unknown_role.yml',
                'isExceptionExpected' => true,
                'databaseUsernames' => array(array('username' => 'user1'), array('username' => 'user2'), array('username' => 'user3')),
                'names' => array()
            ),
        );
    }

    public function getMergedUsers()
    {
        return array(
            'users' =>
                array(
                    0 =>
                        array(
                            'user' =>
                                array(
                                    'first_name' => 'import',
                                    'last_name' => 'import',
                                    'username' => 'import',
                                    'password' => 'IMPRT',
                                    'mail' => 'imported@gmail.com',
                                    'code' => 'IMPORTED'
                                ),
                        ),
                ),
        );
    }

    public function getMergedRoles()
    {
        return array(
            'roles' =>
                array(
                    0 =>
                        array(
                            'role' =>
                                array(
                                    'name' => 'mergedrole',
                                    'translation' => 'totottoo',
                                    'is_base_role' => true,
                                ),
                        )
                )
        );
    }
} 
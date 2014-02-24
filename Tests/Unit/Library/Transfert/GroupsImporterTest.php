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
        $merger = $this->mock('Claroline\CoreBundle\Library\Transfert\Merger');
        $this->importer = new GroupsImporter($this->om, $merger);
    }

    /**
     *  @dataProvider validateProvider
     */
    public function testValidate($path, $isExceptionExpected, $usernames, $names)
    {
        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $groupRepo = $this->mock('Claroline\CoreBundle\Repository\GroupRepository');
        $this->om->shouldReceive('getRepository')->with('Claroline\CoreBundle\Entity\Group')->andReturn($groupRepo);
        $groupRepo->shouldReceive('findNames')->andReturn($names);

        $userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->om->shouldReceive('getRepository')->with('Claroline\CoreBundle\Entity\User')->andReturn($userRepo);
        $userRepo->shouldReceive('findUsernames')->andReturn($usernames);

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
                'usernames' => array(),
                'names' => array()
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/groups/existing_name.yml',
                'isExceptionExpected' => true,
                'usernames' => array(),
                'names' => array()
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/groups01.yml',
                'isExceptionExpected' => true,
                'usernames' => array(),
                'names' => array('name1')
            )
        );
    }
} 
<?php

namespace Claroline\CoreBundle\Library;

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Mockery as m;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\WorkspacePropertiesImporter;
use Symfony\Component\Yaml\Yaml;

class WorkspacePropertiesImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->importer = new WorkspacePropertiesImporter($this->om);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path, $isExceptionExpected, $isUserInDatabase, $isFull, $codeExists)
    {
        $ds = DIRECTORY_SEPARATOR;
        $data = Yaml::parse(file_get_contents($path.$ds.'manifest.yml'));
        $properties['properties'] = $data['properties'];

        if ($isExceptionExpected) {
            $this->setExpectedException('Exception');
        }

        m::getConfiguration()->allowMockingNonExistentMethods(true);

        //init importer
        $this->importer->setRootPath($path);
        $resolver = new Resolver($path);
        $this->importer->setConfiguration($resolver->resolve($data));
        //objectManager
        $wsRepo = $this->mock('Claroline\CoreBundle\Repository\WorkspaceRepository');
        $this->om->shouldReceive('getRepository')
            ->with('Claroline\CoreBundle\Entity\Workspace\Workspace')
            ->andReturn($wsRepo);

        if ($codeExists) {
            $wsRepo->shouldReceive('findOneByCode')->once()
                ->with($properties['properties']['code'])
                ->andThrow('Exception');
        } else {
            $wsRepo->shouldReceive('findOneByCode')->once()
                ->with($properties['properties']['code'])
                ->andReturn('ws');
        }

        $userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');

        if ($isUserInDatabase && !$isFull) {
            $this->om->shouldReceive('getRepository')->andReturn($userRepo);
            $userRepo->shouldReceive('findOneByUsername')->andReturn('user');
        }

        if (!$isUserInDatabase && !$isFull) {
            $this->om->shouldReceive('getRepository')->andReturn($userRepo);
            $userRepo->shouldReceive('findOneByUsername')->andThrow('Exception');
        }

        $this->importer->validate($properties);
    }

    public function validateProvider()
    {
        //isFull = with owner section

        return array(
            //full correct configuration, everything is the config file
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full',
                'isExceptionExpected' => false,
                'isUserInDatabase' => false,
                'isFull' => true,
                'codeExists' => false,
            ),
            //full correct configuration, owner is already in the database
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full',
                'isExceptionExpected' => false,
                'isUserInDatabase' => true,
                'isFull' => true,
                'codeExists' => false,
            ),
            //minimal correct configuration, owner is already in the database
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/minimal',
                'isExceptionExpected' => false,
                'isUserInDatabase' => true,
                'isFull' => false,
                'codeExists' => false,
            ),
            //minimal configuration, no owner section, owner not in database
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/minimal',
                'isExceptionExpected' => true,
                'isUserInDatabase' => false,
                'isFull' => false,
                'codeExists' => false,
            ),
            //full configuration, workspace owner and member owner missmatch
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/wrong_owner',
                'isExceptionExpected' => true,
                'isUserInDatabase' => true,
                'isFull' => true,
                'codeExists' => false,
            ),
            //minimal configuration, workspace code already exists
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/minimal',
                'isExceptionExpected' => true,
                'isUserInDatabase' => true,
                'isFull' => false,
                'codeExists' => true,
            ),
        );
    }
}

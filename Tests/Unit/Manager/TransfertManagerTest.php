<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;


use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\Yaml\Yaml;

class TransfertManagerTest extends MockeryTestCase
{
    private $manager;
    private $listImporters;
    private $workspaceImporter;
    private $userImporter;
    private $groupImporter;

    protected function setUp(){
        parent::setUp();
        $this->workspaceImporter = $this->mock('Claroline\CoreBundle\Library\Transfert\WorkspacePropertiesImporter');
        $this->userImporter = $this->mock('Claroline\CoreBundle\Library\Transfert\UsersImporter');
        $this->groupImporter = $this->mock('Claroline\CoreBundle\Library\Transfert\GroupsImporter');
        $this->manager = new TransfertManager(
            $this->workspaceImporter,
            $this->userImporter,
            $this->groupImporter
        );
    }

    public function testImportWorkspace()
    {
        $this->manager->importWorkspace(__DIR__.'/../../Stub/transfert');
    }
} 
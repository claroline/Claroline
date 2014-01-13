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



use Claroline\MigrationBundle\Tests\MockeryTestCase;

class TransfertManagerTest extends MockeryTestCase
{
    private $manager;

    protected function setUp(){
        parent::setUp();
        $this->manager = new TransfertManager();
    }

    public function testSupport()
    {
        $importer = $this->mock('Claroline\CoreBundle\Library\Transfert\ImporterInterface');
        $importer->shouldReceive('supports')->once()->with('user')->andReturn(false);
        $this->manager->addImporter($importer);
        $this->manager->importWorkspace();
    }
} 
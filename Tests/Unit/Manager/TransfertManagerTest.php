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
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\HomeImporter;

class TransfertManagerTest extends MockeryTestCase
{
    private $manager;

    protected function setUp()
    {
        parent::setUp();

        $this->manager = new TransfertManager();
        $homeConfigBuilder = new HomeImporter(array('roles & co'));
        $this->manager->addImporter($homeConfigBuilder);
    }

    public function testImportWorkspace()
    {
        $this->manager->importWorkspace(__DIR__.'/../../Stub/transfert');
    }
} 
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
use Claroline\CoreBundle\Library\Transfert\WorkspacePropertiesImporter;

class WorkspacePropertiesImporterTest extends MockeryTestCase {
    private $workspacePropertiesImporter;
    private $userManager;

    protected function setUp(){
        parent::setUp();
        $this->userManager = $this->mock('Claroline\CoreBundle\Manager\UserManager');
        $this->workspacePropertiesImporter = new WorkspacePropertiesImporter(
            $this->userManager
        );
    }

    public function testValidate()
    {
        $data = array(
            'properties' => array(
                'name' => 'Anglais',
                'code' => 'EN',
                'visible' => true,
                'selfregistration' => true,
                'owner' => 'ezs'
            )
        );
        $result = $this->workspacePropertiesImporter->validate($data);
        $this->assertEquals($result, true);
    }

} 
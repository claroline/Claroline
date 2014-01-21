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

class WorkspacePropertiesImporterTest extends MockeryTestCase {

    protected function setUp(){
        parent::setUp();
        $this->manager = new TransfertManager();
    }

} 
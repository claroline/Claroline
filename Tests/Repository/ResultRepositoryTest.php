<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Repository;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class ResultRepositoryTest extends TransactionalTestCase
{
    public function testFindByUserAndWorkspace()
    {
        $om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        //$repo->findByUserAndWorkspace();
    }
}

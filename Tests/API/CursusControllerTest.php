<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBubdle\Tests\API;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CursusBundle\Library\Testing\CursusPersister;

class CursusControllerTest extends TransactionalTestCase
{
    /** @var Persister */
    private $persister;
    /** @var CursusPersister */
    private $cursusPersister;

    /*
     * Cursus structure :
     *
     *      rootCursusA
     *          |---- cursusA1
     *          |         |---- cursusA11 (Course)
     *          |         |         * sessionA11A
     *          |         |         * sessionA11B
     *          |         |         * sessionA11C
     *          |         |---- cursusA12 (Course)
     *          |         |---- cursusA12 (LOCKED)
     *          |         |         |---- cursus
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     */
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
        $this->cursusPersister = $this->client->getContainer()->get('claroline.library.testing.cursuspersister');
//        $this->john = $this->persister->user('john');
//        $roleAdmin = $this->persister->role('ROLE_ADMIN');
//        $this->admin = $this->persister->user('admin');
//        $this->admin->addRole($roleAdmin);
//        $this->persister->persist($this->admin);
//        $this->persister->flush();
    }
}

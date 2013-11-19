<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Badge;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class BadgeControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
//        $this->client->followRedirects();
//        $this->loadPlatformRolesFixture();
    }

    public function testAccessToPage()
    {
        $this->markTestSkipped('Test suite does not support functional tests for now');
//        $urls = array(
//            '/admin/badges',
//            '/admin/badges/add'
//        );
//
//        $this->loadUserData(array('admin' => 'admin'));
//        $this->logUser($this->getUser('admin'));
//
//        foreach ($urls as $url) {
//            $this->client->request('GET', $url);
//            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
//        }
    }
}

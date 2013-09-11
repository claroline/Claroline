<?php

namespace Claroline\CoreBundle\Controller\Badge;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class BadgeControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRolesFixture();
    }

    public function testAccessToPage()
    {
        $urls = array(
            '/admin/badges',
            '/admin/badges/add'
        );

        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));

        foreach ($urls as $url) {
            $this->client->request('GET', $url);
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        }
    }
}

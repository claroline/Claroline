<?php

namespace Claroline\CoreBundle\Tests\API\Admin;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

class LocaleControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    /*
     * This test relies on the configuration file app/config/platform_options.yml.
     * There is no platform_options.yml test file yet afaik
     */
    public function testGetAvailableLanguagesAction()
    {
        $this->client->request('GET', '/api/locales/available');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $expected = ['value' => 'fr', 'label' => 'Français'];
        $this->assertEquals($data[0], $expected);
    }
}

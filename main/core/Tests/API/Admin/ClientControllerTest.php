<?php

namespace Claroline\CoreBundle\Tests\API\Admin;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

class ClientControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    /**
     * Get the client id and the client secret.
     *
     * @Route("/idsecret.{_format}", name="claro_id_secret", defaults={"_format":"json"})
     */
    public function testGetIdsecretAction()
    {
        $firstWrong = $this->newClient('wrong', ['authorization_code', 'password', 'refresh_token', 'token', 'client_credentials']);
        $imGood = $this->newClient('good', ['password', 'refresh_token']);
        $imMissingStuff = $this->newClient('missing', ['token', 'authorization_code']);
        $this->client->request('GET', '/api/client/public');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->assertEquals($data['name'], 'good');
    }

    /**
     * Check if access token is expired.
     *
     * @Route("/expired.{_format}", name="claro_token_expired", defaults={"_format":"json"})
     */
    public function testGetExpiredAction()
    {
        $this->markTestSkipped('Do something when someone become smart enough to test it');
    }

    /**
     * Allowed grant types: authorization_code, password, refresh_token, token, client_credentials.
     */
    private function newClient($name, $grantTypes)
    {
        $client = $this->persister->OauthClient($name, $grantTypes);
        $this->persister->flush();

        return $client;
    }
}

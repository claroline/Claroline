<?php

namespace Claroline\CoreBubdle\Tests\API\User;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

/**
 * Specific tests for organizations
 * How to run:
 * - create database
 * - php app/console claroline:install --env=test
 * - bin/phpunit vendor/claroline/core-bundle/Tests/API/User/ProfileControllerTest.php -c app/phpunit.xml.
 */
class ProfileControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    public function testGetFacetsAction()
    {
        //look at creation fields method
        $this->createFields();
        $user = $this->persister->user('user');
        $this->login($user);
        $this->client->request('GET', "/api/profile/{$user->getId()}/facets");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        //only facetA is visible for user
        $this->assertEquals(1, count($data));
        $this->assertEquals('facetA', $data[0]['name']);

        /*
         * panelA is editable
         * panelB is read only
         * panelC is hidden
         * => only 2 panels should be there
         */

         $this->assertEquals(2, count($data[0]['panels']));

        //fieldA is editable
         $this->assertEquals('fieldA', $data[0]['panels'][0]['fields'][0]['name']);
        $this->assertEquals(true, $data[0]['panels'][0]['fields'][0]['is_editable']);

        //fieldB is readonly
         $this->assertEquals('fieldB', $data[0]['panels'][1]['fields'][0]['name']);
        $this->assertEquals(false, $data[0]['panels'][1]['fields'][0]['is_editable']);
    }

    public function testGetProfileLinksAction()
    {
        $user = $this->persister->user('user');
        $this->login($user);
        $this->client->request('GET', "/api/profile/{$user->getId()}/links");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['name'], 'socialmedia_wall');
    }

    public function testPutFieldsAction()
    {
        $fields = $this->createFields();
        $user = $this->persister->user('user');
        $this->login($user);

        $values = array(
            array(
                'id' => $fields[0]->getId(),
                'user_field_value' => 'value',
            ),
        );

        $data['fields'] = $values;

        $this->client->request('PUT', "/api/profile/{$user->getId()}/fields", $data);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $values = array(
            array(
                'id' => $fields[1]->getId(),
                'user_field_value' => 'value',
            ),
        );

        $data['fields'] = $values;

        $this->client->request('PUT', "/api/profile/{$user->getId()}/fields", $data);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    private function createFields()
    {
        $facetA = $this->persister->facet('facetA', true, true);
        $facetB = $this->persister->facet('facetB', true, true);
        $panelA = $this->persister->panelFacet($facetA, 'panelA', false);
        $panelB = $this->persister->panelFacet($facetA, 'panelB', false);
        $panelC = $this->persister->panelFacet($facetA, 'panelC', false);
        $fieldA = $this->persister->fieldFacet($panelA, 'fieldA', 'text');
        $fieldB = $this->persister->fieldFacet($panelB, 'fieldB', 'text');
        $fieldC = $this->persister->fieldFacet($panelC, 'fieldC', 'text');

        $container = $this->client->getContainer();
        $role = $this->persister->role('ROLE_USER');
        $container->get('claroline.manager.facet_manager')->setFacetRoles($facetA, array($role));
        $container->get('claroline.manager.facet_manager')->setPanelFacetRole($panelA, $role, true, true);
        $container->get('claroline.manager.facet_manager')->setPanelFacetRole($panelB, $role, true, false);
        $container->get('claroline.manager.facet_manager')->setPanelFacetRole($panelC, $role, false, false);

        return array($fieldA, $fieldB, $fieldC);
    }
}

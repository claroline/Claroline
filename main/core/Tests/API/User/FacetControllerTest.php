<?php

namespace Claroline\CoreBundle\Tests\API\User;

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

/**
 * Specific tests for organizations
 * How to run:
 * - create database
 * - php app/console claroline:install --env=test
 * - bin/phpunit vendor/claroline/core-bundle/Tests/API/User/FacetControllerTest.php -c app/phpunit.xml.
 */
class FacetControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    public function testControllerIsProtected()
    {
        $user = $this->persister->user('user');
        $this->login($user);
        $this->client->request('GET', '/api/facets.json');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $manager = $this->createManager();
        $this->login($manager);
        $this->client->request('GET', '/api/facets.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetFacetsAction()
    {
        $this->persister->facet('facet', true, true);
        $manager = $this->createManager();
        $this->login($manager);
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['name'], 'facet');
    }

    public function testCreateFacetAction()
    {
        $manager = $this->createManager();
        $this->login($manager);

        $fields = [
            'name' => 'facet',
            'force_creation_form' => false,
            'is_main' => true,
        ];

        $form = ['facet' => $fields];
        $this->client->request('POST', '/api/facet/create', $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['name'], 'facet');
    }

    public function testEditFacetAction()
    {
        $facet = $this->persister->facet('facet', true, true);
        $manager = $this->createManager();
        $this->login($manager);

        $fields = [
            'name' => 'newFacet',
            'force_creation_form' => false,
            'is_main' => false,
        ];

        $form = ['facet' => $fields];
        $this->client->request('PUT', "/api/facet/{$facet->getId()}", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['name'], 'newFacet');
        $this->assertEquals($data['force_creation_form'], false);
        $this->assertEquals($data['is_main'], false);
    }

    public function testDeleteFacetAction()
    {
        $facet = $this->persister->facet('facet', true, true);
        $manager = $this->createManager();
        $this->login($manager);
        $this->client->request('DELETE', "/api/facet/{$facet->getId()}");
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data, []);
    }

    public function testSetFacetRolesAction()
    {
        $facet = $this->persister->facet('facet', true, true);
        $manager = $this->createManager();
        $roles[] = $this->persister->role('ROLE_1');
        $roles[] = $this->persister->role('ROLE_2');
        $this->persister->flush();
        $this->login($manager);
        $queryString = "?ids[]={$roles[0]->getId()}&ids[]={$roles[1]->getId()}";
        $this->client->request('PUT', "/api/facet/{$facet->getId()}/roles{$queryString}");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(count($data['roles']), 2);
    }

    public function testCreateFacetPanelAction()
    {
        $facet = $this->persister->facet('facet', true, true);
        $manager = $this->createManager();

        $fields = [
            'name' => 'panel',
            'is_default_collapsed' => false,
        ];

        $form = ['panel' => $fields];
        $this->login($manager);
        $this->client->request('POST', "/api/facet/{$facet->getId()}/panel/create", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['name'], 'panel');
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(count($data[0]['panels']), 1);
    }

    public function testEditFacetPanelAction()
    {
        $facet = $this->persister->facet('facet', true, true);
        $panel = $this->persister->panelFacet($facet, 'panel', false);
        $manager = $this->createManager();

        $fields = [
            'name' => 'panel_new',
            'is_default_collapsed' => false,
        ];

        $form = ['panel' => $fields];
        $this->login($manager);
        $this->client->request('PUT', "/api/facet/panel/{$panel->getId()}", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['name'], 'panel_new');
    }

    public function testDeletePanelFacetAction()
    {
        $manager = $this->createManager();
        $facet = $this->persister->facet('facet', true, true);
        $panel = $this->persister->panelFacet($facet, 'panel', false);
        $this->login($manager);
        $this->client->request('DELETE', "/api/facet/panel/{$panel->getId()}");
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(count($data[0]['panels']), 0);
    }

    public function testCreateFieldFacetAction()
    {
        $manager = $this->createManager();
        $facet = $this->persister->facet('facet', true, true);
        $panel = $this->persister->panelFacet($facet, 'panel', false);
        $this->login($manager);
        $form['field'] = [
            'name' => 'text',
            'type' => FieldFacet::STRING_TYPE,
        ];
        $this->client->request('POST', "/api/facet/panel/{$panel->getId()}/field/create", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->assertEquals($data['name'], 'text');
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['panels'][0]['fields'][0]['name'], 'text');
    }

    public function testEditFieldFacetAction()
    {
        $manager = $this->createManager();
        $facet = $this->persister->facet('facet', true, true);
        $panel = $this->persister->panelFacet($facet, 'panel', false);
        $field = $this->persister->fieldFacet($panel, 'myname', FieldFacet::STRING_TYPE);
        $this->login($manager);
        $form['field'] = [
            'name' => 'new_text',
            'type' => FieldFacet::STRING_TYPE,
        ];
        $this->client->request('PUT', "/api/facet/panel/field/{$field->getId()}", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['name'], 'new_text');
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['panels'][0]['fields'][0]['name'], 'new_text');
    }

    public function testDeleteFieldFacetAction()
    {
        $this->markTestSkipped('Not implemented yet');
    }

    public function testCreateFieldOptionsAction()
    {
        $manager = $this->createManager();
        $facet = $this->persister->facet('facet', true, true);
        $panel = $this->persister->panelFacet($facet, 'panel', false);
        $field = $this->persister->fieldFacet($panel, 'myname', FieldFacet::STRING_TYPE);
        $form['choice'] = ['label' => 'choice'];
        $this->login($manager);
        $this->client->request('POST', "/api/facet/panel/field/{$field->getId()}/choice", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data['label'], 'choice');
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['panels'][0]['fields'][0]['field_facet_choices'][0]['label'], 'choice');
    }

    public function testDeleteFieldOptionsAction()
    {
        $manager = $this->createManager();
        $facet = $this->persister->facet('facet', true, true);
        $panel = $this->persister->panelFacet($facet, 'panel', false);
        $field = $this->persister->fieldFacet($panel, 'myname', FieldFacet::STRING_TYPE, ['choice']);
        $this->login($manager);
        $choices = $field->getFieldFacetChoices();
        $this->client->request('DELETE', "/api/facet/field/choice/{$choices[0]->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['panels'][0]['fields'][0]['field_facet_choices'], []);
    }

    public function testGetProfilePreferencesAction()
    {
        $manager = $this->createManager();
        $this->login($manager);
        $this->client->request('GET', '/api/facet/profile/preferences');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testPutProfilePreferencesAction()
    {
        $manager = $this->createManager();
        $this->login($manager);
        $roleId = $this->persister->role('ROLE_USER')->getId();
        $params = ['base_data' => 'true', 'mail' => 'false', 'send_message' => 'true', 'phone' => 'false', 'role' => ['id' => $roleId]];
        $data['preferences'] = [$params];
        $this->client->request('PUT', '/api/facet/profile/preferences', $data);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['mail'], false);
        $this->assertEquals($data[0]['send_message'], true);
    }

    public function testOrderPanelsAction()
    {
        $manager = $this->createManager();
        $this->login($manager);
        $facet = $this->persister->facet('facet', true, true);
        $first = $this->persister->panelFacet($facet, 'panel1', false);
        $second = $this->persister->panelFacet($facet, 'panel2', false);
        $qs = "?ids[]={$second->getId()}&ids[]={$first->getId()}";
        $this->client->request('PUT', "/api/facet/{$facet->getId()}/panels/order{$qs}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', '/api/facets.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $first = $this->getPanelByName($data, 'panel1');
        $this->assertEquals('2',  $first['position']);
        $second = $this->getPanelByName($data, 'panel2');
        $this->assertEquals('1',  $second['position']);
    }

    public function testOrderFieldsAction()
    {
        $manager = $this->createManager();
        $this->login($manager);
        $facet = $this->persister->facet('facet', true, true);
        $panel = $this->persister->panelFacet($facet, 'panel', false);
        $first = $this->persister->fieldFacet($panel, 'field1', FieldFacet::STRING_TYPE);
        $second = $this->persister->fieldFacet($panel, 'field2', FieldFacet::STRING_TYPE);
        $qs = "?ids[]={$second->getId()}&ids[]={$first->getId()}";
        $this->client->request('PUT', "/api/facet/panel/{$panel->getId()}/fields/order{$qs}");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $first = $this->getFieldByName($data, 'field1');
        $this->assertEquals('2',  $first['position']);
        $second = $this->getFieldByName($data, 'field2');
        $this->assertEquals('1',  $second['position']);
    }

    private function getPanelByName($facets, $name)
    {
        foreach ($facets as $facet) {
            foreach ($facet['panels'] as $panel) {
                if ($panel['name'] === $name) {
                    return $panel;
                }
            }
        }
    }

    private function getFieldByName($panel, $name)
    {
        foreach ($panel['fields'] as $field) {
            if ($field['name'] === $name) {
                return $field;
            }
        }
    }

    private function createManager()
    {
        $manager = $this->persister->user('manager');
        $this->persister->grantAdminToolAccess($manager, 'user_management');

        return $manager;
    }
}

<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Tests\PluginBundleTestCase;

/**
 * Note : Plugin's FQCNs mentionned in this test case refer to stubs living
 *        in the 'stub/plugin' directory.
 */
class DatabaseHandlerTest extends PluginBundleTestCase
{
    private $em;

    public function setUp()
    {
        parent::setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->client->beginTransaction();
    }

    public function tearDown()
    {
        $this->client->rollback();
        parent :: tearDown();
    }

    public function testInstallApplicationRegistersLaunchersAndRoles()
    {
        $pluginFQCN = 'ValidApplication\TwoLaunchers\ValidApplicationTwoLaunchers';
        $plugin = $this->buildPlugin($pluginFQCN);

        $this->databaseHandler->install($plugin);

        $appRepo = $this->em->getRepository('Claroline\PluginBundle\Entity\Application');
        $apps = $appRepo->findByBundleFQCN($pluginFQCN);

        $this->assertEquals(1, count($apps));

        $launchers = $apps[0]->getLaunchers();
        $this->assertEquals(2, count($launchers));
        $this->assertEquals('route_id_1', $launchers[0]->getRouteId());
        $this->assertEquals('trans_key_1', $launchers[0]->getTranslationKey());
        $this->assertEquals('route_id_2', $launchers[1]->getRouteId());
        $this->assertEquals('trans_key_2', $launchers[1]->getTranslationKey());

        $firstLauncherRoles = $launchers[0]->getAccessRoles();
        $secondLauncherRoles = $launchers[1]->getAccessRoles();
        $this->assertEquals(2, count($firstLauncherRoles));
        $this->assertEquals(1, count($secondLauncherRoles));
        $this->assertEquals('ROLE_TEST_1', $firstLauncherRoles[0]->getName());
        $this->assertEquals('ROLE_TEST_2', $firstLauncherRoles[1]->getName());
        $this->assertEquals('ROLE_TEST_1', $secondLauncherRoles[0]->getName());
    }

    public function testInstallApplicationDoesntDuplicateExistingRole()
    {
        $pluginFQCN = 'ValidApplication\TwoLaunchers\ValidApplicationTwoLaunchers';
        $plugin = $this->buildPlugin($pluginFQCN);

        $this->databaseHandler->install($plugin);

        $roleRepo = $this->em->getRepository('Claroline\SecurityBundle\Entity\Role');
        $roles = $roleRepo->findByName('ROLE_TEST_1');

        $this->assertEquals(1, count($roles));
    }

    public function testInstallApplicationEligibleForIndexInsertsExpectedValues()
    {
        $pluginFQCN = 'ValidApplication\EligibleForIndex1\ValidApplicationEligibleForIndex1';
        $plugin = $this->buildPlugin($pluginFQCN);

        $this->databaseHandler->install($plugin);
        
        $appRepo = $this->em->getRepository('Claroline\PluginBundle\Entity\Application');
        $app = $appRepo->findOneByBundleFQCN($pluginFQCN);
        
        $this->assertEquals('valid_eligible_index_1', $app->getIndexRoute());
        $this->assertEquals(true, $app->isEligibleForPlatformIndex());
        $this->assertEquals(false, $app->isPlatformIndex());
    }
}
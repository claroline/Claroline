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
    }

    public function testInstallApplicationRegistersLaunchersAndRoles()
    {
        $pluginFQCN = 'ValidApplication\TwoLaunchers\ValidApplicationTwoLaunchers';
        $this->requireStubPluginFile($pluginFQCN);

        $this->databaseHandler->install(new $pluginFQCN);

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
        $this->assertEquals(2, count($roles_1));
        $this->assertEquals(1, count($roles_2));
        $this->assertEquals('ROLE_TEST_1', $firstLauncherRoles[0]->getName());
        $this->assertEquals('ROLE_TEST_2', $firstLauncherRoles[1]->getName());
        $this->assertEquals('ROLE_TEST_1', $secondLauncherRoles[0]->getName());
    }

    public function testInstallApplicationDoesntDuplicateExistingRole()
    {
        $pluginFQCN = 'ValidApplication\TwoLaunchers\ValidApplicationTwoLaunchers';
        $this->requireStubPluginFile($pluginFQCN);

        $this->databaseHandler->install(new $pluginFQCN);

        $roleRepo = $this->em->getRepository('Claroline\SecurityBundle\Entity\Role');
        $roles = $roleRepo->findByName('ROLE_TEST_1');

        $this->assertEquals(1, count($roles));
    }

    /**
     * Helper method requiring a plugin file (as the plugin namespace
     * registration isn't made by the DatabaseHandler)
     *
     * @param string $pluginFQCN
     */
    private function requireStubPluginFile($pluginFQCN)
    {
        $pluginFile = $this->pluginDirectory
                . DIRECTORY_SEPARATOR
                . str_replace('\\', DIRECTORY_SEPARATOR, $pluginFQCN)
                . '.php';

        require_once $pluginFile;
    }
}
<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Tests\Fixtures\VirtualPlugins;
use \vfsStream;

class DatabaseHandlerTest extends WebTestCase
{
    private $databaseHandler;
    private $em;

    public function setUp()
    {
        $this->client = self::createClient();

        $fixtures = new VirtualPlugins();
        $fixtures->buildVirtualPluginFiles();
        
        $this->requireVirtualFiles();
        

        $this->databaseHandler = $this->client->getContainer()->get('claroline.plugin.database_handler');
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->client->beginTransaction();
    }
    
    private function requireVirtualFiles()
    {
        require_once vfsStream::url('virtual/plugin/VendorX/FirstPluginBundle/VendorXFirstPluginBundle.php');
        require_once vfsStream::url('virtual/plugin/VendorX/SecondPluginBundle/VendorXSecondPluginBundle.php');
        require_once vfsStream::url('virtual/plugin/VendorY/ThirdPluginBundle/VendorYThirdPluginBundle.php');
        require_once vfsStream::url('virtual/plugin/VendorY/FourthPluginBundle/VendorYFourthPluginBundle.php');
    }

    public function tearDown()
    {
        $this->client->rollback();
    }

    public function testInstallApplicationRegistersLaunchersAndRoles()
    {
        $virtualPlugin = 'VendorX\SecondPluginBundle\VendorXSecondPluginBundle';
        $this->databaseHandler->install(new $virtualPlugin);

        $appRepo = $this->em->getRepository('Claroline\PluginBundle\Entity\Application');
        $apps = $appRepo->findByBundleFQCN('VendorX\SecondPluginBundle\VendorXSecondPluginBundle');

        $this->assertEquals(1, count($apps));

        $launchers = $apps[0]->getLaunchers();
        $this->assertEquals(2, count($launchers));
        $this->assertEquals('route_id_1', $launchers[0]->getRouteId());
        $this->assertEquals('trans_key_1', $launchers[0]->getTranslationKey());
        $this->assertEquals('route_id_2', $launchers[1]->getRouteId());
        $this->assertEquals('trans_key_2', $launchers[1]->getTranslationKey());

        $roles_1 = $launchers[0]->getAccessRoles();
        $roles_2 = $launchers[1]->getAccessRoles();
        $this->assertEquals(2, count($roles_1));
        $this->assertEquals(1, count($roles_2));
        $this->assertEquals('ROLE_TEST_1', $roles_1[0]->getName());
        $this->assertEquals('ROLE_TEST_2', $roles_1[1]->getName());
        $this->assertEquals('ROLE_TEST_1', $roles_2[0]->getName());
    }

    public function testInstallApplicationDoesntDuplicateExistingRole()
    {
        $virtualPlugin = 'VendorX\SecondPluginBundle\VendorXSecondPluginBundle';
        $this->databaseHandler->install(new $virtualPlugin);

        $roleRepo = $this->em->getRepository('Claroline\SecurityBundle\Entity\Role');
        $roles = $roleRepo->findByName('ROLE_TEST_1');

        $this->assertEquals(1, count($roles));
    }
}
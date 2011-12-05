<?php

namespace Claroline\PluginBundle\Installer\Recorder\Writer;

use Claroline\CommonBundle\Library\Testing\TransactionalTestCase;
use Claroline\PluginBundle\Installer\Loader;
use Claroline\PluginBundle\Entity\Plugin;

class DatabaseWriterTest extends TransactionalTestCase
{
    /** @var Claroline\PluginBundle\Installer\Recorder\Writer\DatabaseWriter */
    private $dbWriter;
    
    /** @var Claroline\PluginBundle\Installer\Loader */
    private $loader;
    
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    public function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->dbWriter = $container->get('claroline.plugin.recorder_database_writer');
        $this->loader = $container->get('claroline.plugin.loader');
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->overrideDefaultPluginDirectories($this->loader);
    }
    
    /**
     * @dataProvider pluginPropertiesProvider
     */
    public function testWriterMakesInsertsCommonPropertiesForEachTypeOfPlugin($fqcn, $entityType, $pluginType)
    {
        $plugin = $this->loader->load($fqcn);
        $this->dbWriter->insert($plugin);
        
        $pluginEntity = $this->em
            ->getRepository($entityType)
            ->findOneByBundleFQCN($fqcn);
        
        $this->assertEquals($pluginType, $pluginEntity->getType());
        $this->assertEquals($plugin->getVendorName(), $pluginEntity->getVendorName());
        $this->assertEquals($plugin->getBundleName(), $pluginEntity->getBundleName());
        $this->assertEquals($plugin->getNameTranslationKey(), $pluginEntity->getNameTranslationKey());
        $this->assertEquals($plugin->getDescriptionTranslationKey(), $pluginEntity->getDescriptionTranslationKey());
    }
    
    public function testInsertThenDeleteAPluginLeavesDatabaseUnchanged()
    {
        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->dbWriter->insert($plugin);
        $this->dbWriter->delete('Valid\Simple\ValidSimple');
        
        $extensions = $this->em
            ->getRepository('Claroline\PluginBundle\Entity\Extension')
            ->findOneByBundleFQCN('Valid\Simple\ValidSimple');
        
        $this->assertEquals(0, count($extensions));
    }
    
    public function testInsertThrowsAnExceptionIfPluginEntityIsNotValid()
    {
        $this->setExpectedException('Claroline\PluginBundle\Exception\InstallationException');
        
        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->dbWriter->insert($plugin);
        $this->dbWriter->insert($plugin); // violates unique name constraint
    }
    
    public function testApplicationDetailsAreProperlyPersistedOnInsert()
    {
        $plugin = $this->loader->load('Valid\TwoLaunchers\ValidTwoLaunchers');
        $this->dbWriter->insert($plugin);
        
        $application = $this->em
            ->getRepository('Claroline\PluginBundle\Entity\Application')
            ->findOneByBundleFQCN('Valid\TwoLaunchers\ValidTwoLaunchers');
        
        $this->assertEquals(true, $application->isEligibleForPlatformIndex());
        $this->assertEquals(true, $application->isEligibleForConnectionTarget());
        
        $launchers = $application->getLaunchers();
        
        $this->assertEquals(2, count($launchers));
        $this->assertEquals('route_id_1', $launchers[0]->getRouteId());
        $this->assertEquals('route_id_2', $launchers[1]->getRouteId());
        $this->assertEquals('trans_key_1', $launchers[0]->getTranslationKey());
        $this->assertEquals('trans_key_2', $launchers[1]->getTranslationKey());
        
        $firstLauncherRoles = $launchers[0]->getAccessRoles();
        $secondLauncherRoles = $launchers[1]->getAccessRoles();
        
        $this->assertEquals(2, count($firstLauncherRoles));
        $this->assertEquals(1, count($secondLauncherRoles));
        $this->assertEquals('ROLE_TEST_1', $firstLauncherRoles[0]->getName());
        $this->assertEquals('ROLE_TEST_2', $firstLauncherRoles[1]->getName());
        $this->assertEquals('ROLE_TEST_1', $secondLauncherRoles[0]->getName());
    }
    
    public function testApplicationDetailsAreProperlyRemovedOnDelete()
    {
        $plugin = $this->loader->load('Valid\TwoLaunchers\ValidTwoLaunchers');
        $this->dbWriter->insert($plugin);
        $this->dbWriter->delete('Valid\TwoLaunchers\ValidTwoLaunchers');
        
        $applications = $this->em
            ->getRepository('Claroline\PluginBundle\Entity\Application')
            ->findByBundleFQCN('Valid\TwoLaunchers\ValidTwoLaunchers');
        
        $this->assertEquals(0, count($applications));
        
        $query = "SELECT launcher FROM Claroline\PluginBundle\Entity\ApplicationLauncher launcher "
            . "WHERE launcher.routeId IN ('route_id_1', 'route_id_2')";
        $launchers = $this->em->createQuery($query)->getResult();
        
        $this->assertEquals(0, count($launchers));
    }
    
    public function testIsSavedReturnsExpectedValue()
    {
        $pluginFQCN = 'Valid\Simple\ValidSimple';
        $plugin = $this->loader->load($pluginFQCN);
        
        $this->assertFalse($this->dbWriter->isSaved($pluginFQCN));
        
        $this->dbWriter->insert($plugin);
        
        $this->assertTrue($this->dbWriter->isSaved($pluginFQCN));
    }
    
    public function pluginPropertiesProvider()
    {
        return array(
            array(
                'Valid\Simple\ValidSimple', 
                'Claroline\PluginBundle\Entity\Extension', 
                'Claroline\PluginBundle\AbstractType\ClarolineExtension'
            ),
            array(
                'Valid\Basic\ValidBasic', 
                'Claroline\PluginBundle\Entity\Application', 
                'Claroline\PluginBundle\AbstractType\ClarolineApplication'
            ),
            array(
                'Valid\Elementary\ValidElementary', 
                'Claroline\PluginBundle\Entity\Tool', 
                'Claroline\PluginBundle\AbstractType\ClarolineTool'
            )
        );
    }
    
    private function overrideDefaultPluginDirectories(Loader $loader)
    {
        $ds = DIRECTORY_SEPARATOR;
        $stubDir = __DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}stub{$ds}plugin{$ds}";
        $loader->setPluginDirectories(
            array(
                'extension' => "{$stubDir}extension",
                'application' => "{$stubDir}application",
                'tool' => "{$stubDir}tool"
            )
        );
    }
}
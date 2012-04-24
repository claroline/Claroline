<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Entity\Plugin;

class DatabaseWriterTest extends TransactionalTestCase
{
    /** @var DatabaseWriter */
    private $dbWriter;
    
    /** @var Loader */
    private $loader;
    
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    protected function setUp()
    {
        parent::setUp();
        //$this->markTestSkipped('The MySQLi extension is not available.');
        $container = $this->client->getContainer();
        $this->dbWriter = $container->get('claroline.plugin.recorder_database_writer');
        $this->loader = $container->get('claroline.plugin.loader');
        $this->em = $container->get('doctrine.orm.entity_manager');
        $stubDir = $container->getParameter('claroline.stub_plugin_directory');
        $this->overrideDefaultPluginDirectories($this->loader, $stubDir);
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
            ->getRepository('Claroline\CoreBundle\Entity\Extension')
            ->findOneByBundleFQCN('Valid\Simple\ValidSimple');
        
        $this->assertEquals(0, count($extensions));
    }
    
    public function testInsertThrowsAnExceptionIfPluginEntityIsNotValid()
    {
        $this->setExpectedException('Claroline\CoreBundle\Exception\InstallationException');
        
        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->dbWriter->insert($plugin);
        $this->dbWriter->insert($plugin); // violates unique name constraint
    }
    
    public function testIsSavedReturnsExpectedValue()
    {
        $pluginFQCN = 'Valid\Simple\ValidSimple';
        $plugin = $this->loader->load($pluginFQCN);
        
        $this->assertFalse($this->dbWriter->isSaved($pluginFQCN));
        
        $this->dbWriter->insert($plugin);
        
        $this->assertTrue($this->dbWriter->isSaved($pluginFQCN));
    }
    
    public function testCustomResourceTypesArePersisted()
    {
        //$this->markTestSkipped("should be removed ?");
        $pluginFQCN = 'Valid\WithCustomResources\ValidWithCustomResources';
        $plugin = $this->loader->load($pluginFQCN);
        $this->dbWriter->insert($plugin);
       /* 
        $expectedTypes = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findByBundle('WithCustomResources');*/
        
        //var_dump($this->client->getContainer()->getParameter('resource.service.list'));
        //$type = $this->client->getContainer()->get('vendorx.resourcex.manager')->geResourceType();
        //var_dump($type);
        
        $expectedTypes = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findAll();

        $this->assertEquals(4, count($expectedTypes));
        $this->assertEquals('Valid\WithCustomResources\Entity\ResourceX', $expectedTypes[2]->getType());
        $this->assertEquals('Valid\WithCustomResources\Entity\ResourceY', $expectedTypes[3]->getType());
    }
    
    public function pluginPropertiesProvider()
    {
        return array(
            array(
                'Valid\Simple\ValidSimple',
                'Claroline\CoreBundle\Entity\Extension',
                'Claroline\CoreBundle\Library\Plugin\ClarolineExtension'
            ),
            array(
                'Valid\Basic\ValidBasic',
                'Claroline\CoreBundle\Entity\Tool',
                'Claroline\CoreBundle\Library\Plugin\ClarolineTool'
            )
        );
    }
    
    private function overrideDefaultPluginDirectories(Loader $loader, $stubDir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $loader->setPluginDirectories(
            array(
                'extension' => "{$stubDir}{$ds}extension",
                'tool' => "{$stubDir}{$ds}tool"
            )
        );
    }
}
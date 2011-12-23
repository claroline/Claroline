<?php

namespace Claroline\PluginBundle\Installer\Recorder\Writer;

use Claroline\CommonBundle\Test\TransactionalTestCase;
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
                'tool' => "{$stubDir}tool"
            )
        );
    }
}
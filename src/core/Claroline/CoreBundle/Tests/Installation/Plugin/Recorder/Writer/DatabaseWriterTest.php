<?php

namespace Claroline\CoreBundle\Installation\Plugin\Recorder\Writer;

use Claroline\CoreBundle\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Installation\Plugin\Loader;
use Claroline\CoreBundle\Entity\Plugin;

class DatabaseWriterTest extends TransactionalTestCase
{
    /** @var DatabaseWriter */
    private $dbWriter;
    
    /** @var Claroline\CoreBundle\Installation\Plugin\Loader */
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
    
    public function pluginPropertiesProvider()
    {
        return array(
            array(
                'Valid\Simple\ValidSimple',
                'Claroline\CoreBundle\Entity\Extension',
                'Claroline\CoreBundle\AbstractType\ClarolineExtension'
            ),
            array(
                'Valid\Basic\ValidBasic',
                'Claroline\CoreBundle\Entity\Tool',
                'Claroline\CoreBundle\AbstractType\ClarolineTool'
            )
        );
    }
    
    private function overrideDefaultPluginDirectories(Loader $loader)
    {
        $ds = DIRECTORY_SEPARATOR;
        $stubDir = __DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}";
        $loader->setPluginDirectories(
            array(
                'extension' => "{$stubDir}extension",
                'tool' => "{$stubDir}tool"
            )
        );
    }
}
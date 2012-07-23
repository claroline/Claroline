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
        $container = $this->client->getContainer();
        $this->dbWriter = $container->get('claroline.plugin.recorder_database_writer');
        $this->loader = $container->get('claroline.plugin.loader');
        $pluginDirectory = $container->getParameter('claroline.stub_plugin_directory');
        $this->loader = new Loader($pluginDirectory);
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * @dataProvider pluginProvider
     */
    public function testWriterCorrectlyPersistsPluginProperties($fqcn)
    {
        $plugin = $this->loader->load($fqcn);
        $this->dbWriter->insert($plugin);

        $pluginEntity = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByBundleFQCN($fqcn);

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

        $plugins = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByBundleFQCN('Valid\Simple\ValidSimple');

        $this->assertEquals(0, count($plugins));
    }

    public function testInsertThrowsAnExceptionIfPluginEntityIsNotValid()
    {
        $this->setExpectedException('RuntimeException');

        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->dbWriter->insert($plugin);
        $this->dbWriter->insert($plugin); // violates unique name constraint
    }

    public function testIsSavedReturnsExpectedValue()
    {
        $pluginFqcn = 'Valid\Simple\ValidSimple';
        $plugin = $this->loader->load($pluginFqcn);

        $this->assertFalse($this->dbWriter->isSaved($pluginFqcn));

        $this->dbWriter->insert($plugin);

        $this->assertTrue($this->dbWriter->isSaved($pluginFqcn));
    }

    public function testCustomResourceTypesArePersisted()
    {
        $pluginFqcn = 'Valid\WithCustomResources\ValidWithCustomResources';
        $plugin = $this->loader->load($pluginFqcn);
        $this->dbWriter->insert($plugin);

        $dql = "
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            JOIN rt.plugin p
            WHERE p.bundleName = 'WithCustomResources'
        ";
        $pluginResourceTypes = $this->em->createQuery($dql)->getResult();

        $this->assertEquals(2, count($pluginResourceTypes));
        $this->assertEquals('ResourceA', $pluginResourceTypes[0]->getType());
        $this->assertEquals('ResourceB', $pluginResourceTypes[1]->getType());
    }

    public function pluginProvider()
    {
        return array(
            array('Valid\Simple\ValidSimple'),
            array('Valid\Custom\ValidCustom')
        );
    }
}
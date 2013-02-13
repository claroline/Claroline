<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class DatabaseWriterTest extends TransactionalTestCase
{
    /** @var DatabaseWriter */
    private $dbWriter;

    /** @var Loader */
    private $loader;

    /** @var Validator */
    private $validator;

    /** @var Doctrine\ORM\EntityManager */
    private $em;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->dbWriter = $container->get('claroline.plugin.recorder_database_writer');
        $pluginDirectory = $container->getParameter('claroline.stub_plugin_directory');
        $this->loader = new Loader($pluginDirectory);
        $this->validator = $container->get('claroline.plugin.validator');
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * @dataProvider pluginProvider
     */
    public function testWriterCorrectlyPersistsPluginProperties($fqcn)
    {
        $plugin = $this->loader->load($fqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $pluginEntity = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByBundleFQCN($fqcn);

        $this->assertEquals($plugin->getVendorName(), $pluginEntity->getVendorName());
        $this->assertEquals($plugin->getBundleName(), $pluginEntity->getBundleName());
    }

    public function testInsertThenDeleteAPluginLeavesDatabaseUnchanged()
    {
        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $this->dbWriter->delete('Valid\Simple\ValidSimple');

        $plugins = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByBundleFQCN('Valid\Simple\ValidSimple');

        $this->assertEquals(0, count($plugins));
    }

    public function testInsertThrowsAnExceptionIfPluginEntityIsNotValid()
    {
        $this->setExpectedException('Doctrine\DBAL\DBALException');

        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        // violates unique name constraint
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
    }

    public function testIsSavedReturnsExpectedValue()
    {
        $pluginFqcn = 'Valid\Simple\ValidSimple';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);

        $this->assertFalse($this->dbWriter->isSaved($pluginFqcn));

        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());

        $this->assertTrue($this->dbWriter->isSaved($pluginFqcn));
    }

    public function testCustomResourceTypesArePersisted()
    {
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithCustomResources{$ds}Entity{$ds}ResourceA.php";
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithCustomResources{$ds}Entity{$ds}ResourceB.php";

        $pluginFqcn = 'Valid\WithCustomResources\ValidWithCustomResources';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());

        $dql = "
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            JOIN rt.plugin p
            WHERE p.bundleName = 'WithCustomResources'
        ";
        $pluginResourceTypes = $this->em->createQuery($dql)->getResult();

        $this->assertEquals(2, count($pluginResourceTypes));
        $this->assertEquals('ResourceA', $pluginResourceTypes[0]->getName());
        $this->assertEquals('ResourceB', $pluginResourceTypes[1]->getName());
    }

    public function testResourceIconsArePersisted()
    {
        $this->markTestSkipped('Search the icon in the web folder and it isn\'t ');
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithResourceIcon{$ds}Entity{$ds}ResourceX.php";

        $pluginFqcn = 'Valid\WithResourceIcon\ValidWithResourceIcon';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());

        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceIcon ri
            WHERE ri.iconLocation LIKE '%validwithlargeicon%'
        ";

        $resourceIcon = $this->em->createQuery($dql)->getResult();
        $this->assertEquals(1, count($resourceIcon));
        $this->assertEquals($resourceIcon[0]->getIconType()->getIconType(), 'type');
    }

    public function testCustomActionsArePersited()
    {
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithCustomActions{$ds}Entity{$ds}ResourceX.php";

        $pluginFqcn = 'Valid\WithCustomActions\ValidWithCustomActions';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());

        $dql = "
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.name = 'ResourceXCustom'
        ";

        $resourceType = $this->em->createQuery($dql)->getResult();
        $this->assertEquals(1, count($resourceType));

        $dql = "
            SELECT ca FROM Claroline\CoreBundle\Entity\Resource\ResourceTypeCustomAction ca,
            Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.name = 'ResourceXCustom'
            AND ca.resourceType = '{$resourceType[0]->getId()}'
        ";

        $customAction = $this->em->createQuery($dql)->getResult();
        $this->assertEquals(1, count($customAction));
        $customName = $customAction[0]->getAction();
        $this->assertEquals('open', $customName);
    }

    public function testPluginIconIsPersisted()
    {
        $pluginFqcn = 'Valid\WithIcon\ValidWithIcon';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());

        $dql = "
            SELECT p FROM Claroline\CoreBundle\Entity\Plugin p
            WHERE p.bundleName LIKE '%Icon'
        ";

        $pluginEntity = $this->em->createQuery($dql)->getResult();
        $this->assertContains('icon.gif', $pluginEntity[0]->getIcon());
    }

    public function testPluginToolIsPersisted()
    {
        $pluginFqcn = 'Valid\WithTools\ValidWithTools';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());

        $dql = "
            SELECT t FROM Claroline\CoreBundle\Entity\Tool\Tool t
            WHERE t.name = 'toolA'
        ";

        $pluginEntity = $this->em->createQuery($dql)->getResult();
        $this->assertEquals(1, count($pluginEntity));
    }

    public function pluginProvider()
    {
        return array(
            array('Valid\Simple\ValidSimple'),
            array('Valid\Custom\ValidCustom')
        );
    }
}

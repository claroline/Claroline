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
        $this->assertEquals('plugin', $pluginEntity->getNameTranslationKey());
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
        $this->setExpectedException('Symfony\Component\Security\Acl\Exception\AclAlreadyExistsException');

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
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}WithCustomResources{$ds}Entity{$ds}ResourceA.php";
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}WithCustomResources{$ds}Entity{$ds}ResourceB.php";

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

    public function testLargeIconsArePersisted()
    {
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}WithLargeIcon{$ds}Entity{$ds}ResourceX.php";

        $pluginFqcn = 'Valid\WithLargeIcon\ValidWithLargeIcon';
        $plugin = $this->loader->load($pluginFqcn);
        $this->dbWriter->insert($plugin);

        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceIcon ri
            WHERE ri.largeIcon LIKE '%validwithlargeicon%'
            ";

        $resourceIcon = $this->em->createQuery($dql)->getResult();
        $this->assertEquals(1, count($resourceIcon));
        $this->assertEquals($resourceIcon[0]->getIconType()->getIconType(), 'type');
    }

    public function testSmallIconsArePersisted()
    {
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}WithSmallIcon{$ds}Entity{$ds}ResourceX.php";

        $pluginFqcn = 'Valid\WithSmallIcon\ValidWithSmallIcon';
        $plugin = $this->loader->load($pluginFqcn);
        $this->dbWriter->insert($plugin);

        $dql = "
            SELECT ri FROM Claroline\CoreBundle\Entity\Resource\ResourceIcon ri
            WHERE ri.smallIcon LIKE '%validwithsmallicon%'
            ";

        $resourceIcon = $this->em->createQuery($dql)->getResult();
        $this->assertEquals(1, count($resourceIcon));
        $this->assertEquals($resourceIcon[0]->getIconType()->getIconType(), 'type');
    }

    public function testCustomActionsArePersited()
    {
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}WithCustomActions{$ds}Entity{$ds}ResourceX.php";

        $pluginFqcn = 'Valid\WithCustomActions\ValidWithCustomActions';
        $plugin = $this->loader->load($pluginFqcn);
        $this->dbWriter->insert($plugin);

        $dql = "
            SELECT rt FROM Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.type = 'ResourceXCustom'";

        $resourceType = $this->em->createQuery($dql)->getResult();
        $this->assertEquals(1, count($resourceType));

        $dql = "
            SELECT ca FROM Claroline\CoreBundle\Entity\Resource\ResourceTypeCustomAction ca,
            Claroline\CoreBundle\Entity\Resource\ResourceType rt
            WHERE rt.type = 'ResourceXCustom'
            AND ca.resourceType = '{$resourceType[0]->getId()}'";

        $customAction = $this->em->createQuery($dql)->getResult();
        $this->assertEquals(1, count($customAction));
        $customName = $customAction[0]->getAction();
        $this->assertEquals('open', $customName);
    }

    public function testPluginIconIsPersisted()
    {
        $pluginFqcn = 'Valid\WithIcon\ValidWithIcon';
        $plugin = $this->loader->load($pluginFqcn);
        $this->dbWriter->insert($plugin);

        $dql = "
            SELECT p FROM Claroline\CoreBundle\Entity\Plugin p
            WHERE p.bundleName LIKE '%Icon'";

        $pluginEntity = $this->em->createQuery($dql)->getResult();
        $this->assertContains('icon.gif', $pluginEntity[0]->getIcon());
    }

    public function pluginProvider()
    {
        return array(
            array('Valid\Simple\ValidSimple'),
            array('Valid\Custom\ValidCustom')
        );
    }
}
<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Symfony\Component\Yaml\Yaml;

class DatabaseWriterTest extends FunctionalTestCase
{
    /** @var DatabaseWriter */
    private $dbWriter;

    /** @var Loader */
    private $loader;

    /** @var Validator */
    private $validator;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->client->getContainer();
        $this->dbWriter = $this->container->get('claroline.plugin.recorder_database_writer');
        $pluginDirectory = $this->container->getParameter('claroline.param.stub_plugin_directory');
        $this->loader = new Loader($pluginDirectory);
        $this->validator = $this->container->get('claroline.plugin.validator');
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
            ->getRepository('ClarolineCoreBundle:Plugin')
            ->findOneByBundleFQCN($fqcn);

        $this->assertEquals($plugin->getVendorName(), $pluginEntity->getVendorName());
        $this->assertEquals($plugin->getBundleName(), $pluginEntity->getBundleName());
        $this->resetTemplate(array($fqcn));
    }

    public function testInsertThenDeleteAPluginLeavesDatabaseUnchanged()
    {
        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $this->dbWriter->delete('Valid\Simple\ValidSimple');

        $plugins = $this->em
            ->getRepository('ClarolineCoreBundle:Plugin')
            ->findOneByBundleFQCN('Valid\Simple\ValidSimple');

        $this->assertEquals(0, count($plugins));
        $this->resetTemplate(array('Valid\Simple\ValidSimple'));
    }

    public function testInsertThrowsAnExceptionIfPluginEntityIsNotValid()
    {
        $this->setExpectedException('Doctrine\DBAL\DBALException');

        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        // violates unique name constraint
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $this->resetTemplate(array('Valid\Simple\ValidSimple'));
    }

    public function testIsSavedReturnsExpectedValue()
    {
        $pluginFqcn = 'Valid\Simple\ValidSimple';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->assertFalse($this->dbWriter->isSaved($pluginFqcn));
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $this->assertTrue($this->dbWriter->isSaved($pluginFqcn));
        $this->resetTemplate(array('Valid\Simple\ValidSimple'));
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
            ORDER BY rt.name
        ";
        $pluginResourceTypes = $this->em->createQuery($dql)->getResult();

        $this->assertEquals(2, count($pluginResourceTypes));
        $this->assertEquals('ResourceA', $pluginResourceTypes[0]->getName());
        $this->assertEquals('ResourceB', $pluginResourceTypes[1]->getName());
        $this->resetTemplate(array('Valid\Simple\ValidSimple'));
    }

    public function testResourceIconsArePersisted()
    {
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
        $this->assertEquals($resourceIcon[0]->getIconType()->getType(), 'type');
    }

    public function testCustomActionsArePersisted()
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
        $this->resetTemplate(array('Valid\WithCustomActions\ValidWithCustomActions'));
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
        $this->resetTemplate(array('Valid\WithIcon\ValidWithIcon'));
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
        $this->resetTemplate(array('Valid\WithTools\ValidWithTools'));
    }

    public function testInsertThenDeleteFileExtension()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'));

        $pluginFqcn = 'Valid\WithFileExtension\ValidWithFileExtension';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $this->em->clear();
        $resourceA = new \Claroline\CoreBundle\Entity\Resource\File();
        $resourceA->setSize(42);
        $resourceA->setName('resourceA');
        $resourceA->setHashName('azerty123');
        $resourceA->setMimeType('foo/bar');

        $manager = $this->container->get('claroline.resource.manager');
        $manager->create($resourceA, $this->getDirectory('user')->getId(), 'resourceA', $this->getUser('user'));
        $resource = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findOneByName('resourceA');
        $this->assertEquals($resource->getResourceType()->getName(), 'resourceA');

        $this->dbWriter->delete('Valid\WithFileExtension\ValidWithFileExtension');
        $resource = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findOneByName('resourceA');
        $this->assertEquals($resource->getResourceType()->getName(), 'file');
        $this->resetTemplate(array('Valid\WithFileExtension\ValidWithFileExtension'));
    }

    public function testInsertTheDeleteToolUpdateDefaultTemplate()
    {
        $container = $this->client->getContainer();
        $archive = new \ZipArchive();
        $archpath = $container->getParameter('claroline.param.templates_directory').'default.zip';
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $oldTools = count($parsedFile['tools_infos']);
        $archive->close();
        $pluginFqcn = 'Valid\WithTools\ValidWithTools';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $newTools = count($parsedFile['tools_infos']);
        $archive->close();
        $this->assertEquals(1, $newTools - $oldTools);
        $this->dbWriter->delete($pluginFqcn);
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $endTools = count($parsedFile['tools_infos']);
        $archive->close();
        $this->assertEquals(0, $oldTools - $endTools);
    }

    public function testInsertThenDeleteWidgetUpdateTemplate()
    {
        $container = $this->client->getContainer();
        $archive = new \ZipArchive();
        $archpath = $container->getParameter('claroline.param.templates_directory').'default.zip';
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $oldWidget = count($parsedFile['tools']['home']['widget']);
        $archive->close();
        $pluginFqcn = 'Valid\WithWidgets\ValidWithWidgets';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $newWidget = count($parsedFile['tools']['home']['widget']);
        $archive->close();
        $this->assertEquals(4, $newWidget - $oldWidget);
        $this->dbWriter->delete($pluginFqcn);
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $endWidget = count($parsedFile['tools']['home']['widget']);
        $archive->close();
        $this->assertEquals(0, $oldWidget - $endWidget);
    }

    public function testInsertThenDeleteResourceTypeUpdateTemplate()
    {
        $container = $this->client->getContainer();
        $archive = new \ZipArchive();
        $archpath = $container->getParameter('claroline.param.templates_directory').'default.zip';
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $oldResources = count($parsedFile['root_perms']['ROLE_WS_MANAGER']['canCreate']);
        $archive->close();
        $pluginFqcn = 'Valid\WithCustomResources\ValidWithCustomResources';
        $plugin = $this->loader->load($pluginFqcn);
        $this->validator->validate($plugin);
        $this->dbWriter->insert($plugin, $this->validator->getPluginConfiguration());
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $newResources = count($parsedFile['root_perms']['ROLE_WS_MANAGER']['canCreate']);
        $archive->close();
        $this->assertEquals(2, $newResources - $oldResources);
        $this->dbWriter->delete($pluginFqcn);
        $archive->open($archpath);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $endResources = count($parsedFile['root_perms']['ROLE_WS_MANAGER']['canCreate']);
        $archive->close();
        $this->assertEquals(0, $oldResources - $endResources);
    }

    public function pluginProvider()
    {
        return array(
            array('Valid\Simple\ValidSimple'),
            array('Valid\Custom\ValidCustom')
        );
    }
}

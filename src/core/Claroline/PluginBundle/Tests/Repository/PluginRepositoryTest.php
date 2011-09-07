<?php

namespace Claroline\PluginBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Entity\BasePlugin;

class PluginRepositoryTest extends WebTestCase
{
    private $client;
    /** @var Claroline\PluginBundle\Repository\PluginRepository */
    private $repository;

    public function setUp()
    {
        $this->client = self::createClient();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository('Claroline\PluginBundle\Entity\AbstractPlugin');
        $this->client->beginTransaction();
    }

    public function tearDown()
    {
        $this->client->rollback();
    }

    public function testCreatePluginInsertsNewPluginRecord()
    {
        $plugin = $this->buildBasePluginEntity(
                'VendorX\TestBundle\VendorXTestBundle',
               'ClarolinePlugin',
               'VendorX',
               'TestBundle',
               'Test',
               'Test description');
        $this->repository->createPlugin($plugin);

        $plugin = $this->repository->findOneByBundleFQCN('VendorX\TestBundle\VendorXTestBundle');

        $this->assertEquals('ClarolinePlugin', $plugin->getType());
        $this->assertEquals('VendorX', $plugin->getVendorName());
        $this->assertEquals('TestBundle', $plugin->getBundleName());
        $this->assertEquals('Test', $plugin->getNameTranslationKey());
        $this->assertEquals('Test description', $plugin->getDescriptionTranslationKey());
    }

    public function testCreatePluginDoesntDuplicateExistingFQCN()
    {
        $this->setExpectedException('Claroline\PluginBundle\Repository\Exception\ModelException');

        $plugin1 = $this->buildBasePluginEntity('VendorX\TestBundle\VendorXTestBundle', '', '', '', '', '');
        $plugin2 = $this->buildBasePluginEntity('VendorX\TestBundle\VendorXTestBundle', '', '', '', '', '');

        $this->repository->createPlugin($plugin1);
        $this->repository->createPlugin($plugin2);
    }

    public function testDeletePluginRemovesPluginRecord()
    {
        $plugin = $this->buildBasePluginEntity('VendorX\TestBundle\VendorXTestBundle', '', '', '', '', '');
        $this->repository->createPlugin($plugin);
        $this->repository->deletePlugin('VendorX\TestBundle\VendorXTestBundle');

        $plugin = $this->repository->findOneByBundleFQCN('VendorX\TestBundle\VendorXTestBundle');
        $this->assertEquals(null, $plugin);
    }

    private function buildBasePluginEntity($FQCN, $type, $vendor, $bundle, $name, $desc)
    {
        $pluginEntity = new BasePlugin();
        $pluginEntity->setBundleFQCN($FQCN);
        $pluginEntity->setType($type);
        $pluginEntity->setVendorName($vendor);
        $pluginEntity->setBundleName($bundle);
        $pluginEntity->setNameTranslationKey($name);
        $pluginEntity->setDescriptionTranslationKey($desc);

        return $pluginEntity;
    }
}
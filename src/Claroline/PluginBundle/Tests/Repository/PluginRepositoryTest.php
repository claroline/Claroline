<?php

namespace Claroline\PluginBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Entity\Plugin;

class PluginRepositoryTest extends WebTestCase
{
    private $client;
    /** @var Claroline\PluginBundle\Repository\PluginRepository */
    private $repository;

    public function setUp()
    {
        $this->client = self::createClient();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository('Claroline\PluginBundle\Entity\Plugin');
        $this->client->beginTransaction();
    }

    public function tearDown()
    {
        $this->client->rollback();
    }

    public function testCreatePluginInsertsNewPluginRecord()
    {
        $this->repository->createPlugin('VendorX\TestBundle\VendorXTestBundle',
                                        'VendorX',
                                        'TestBundle',
                                        'Test',
                                        'Test description');

        $plugin = $this->repository->findOneByBundleFQCN('VendorX\TestBundle\VendorXTestBundle');

        $this->assertEquals('VendorX', $plugin->getVendorName());
        $this->assertEquals('TestBundle', $plugin->getBundleName());
        $this->assertEquals('Test', $plugin->getNameTranslationKey());
        $this->assertEquals('Test description', $plugin->getDescriptionTranslationKey());
    }

    public function testCreatePluginDoesntDuplicateExistingFQCN()
    {
        $this->setExpectedException('Claroline\PluginBundle\Repository\Exception\ModelException');
        $this->repository->createPlugin('VendorX\TestBundle\VendorXTestBundle', '', '', '', '');
        $this->repository->createPlugin('VendorX\TestBundle\VendorXTestBundle', '', '', '', '');
    }

    public function testDeletePluginRemovesPluginRecord()
    {
        $this->repository->createPlugin('VendorX\TestBundle\VendorXTestBundle', '', '', '', '');
        $this->repository->deletePlugin('VendorX\TestBundle\VendorXTestBundle');
        $plugin = $this->repository->findOneByBundleFQCN('VendorX\TestBundle\VendorXTestBundle');
        $this->assertEquals(null, $plugin);
    }
}

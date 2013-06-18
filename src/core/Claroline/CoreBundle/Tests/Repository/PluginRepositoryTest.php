<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;
use Claroline\CoreBundle\Entity\Plugin;

class PluginRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\PluginRepository */
    public static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Plugin');
        $plugin = new Plugin();
        $plugin->setVendorName('Vendor');
        $plugin->setBundleName('Bundle');
        $plugin->setHasOptions(false);
        $plugin->setIcon('default');
        self::$em->persist($plugin);
        self::$em->flush();
    }

    public function testFindOneByBundleFQCN()
    {
        $plugin = self::$repo->findOneByBundleFQCN('Vendor\Bundle');
        $this->assertEquals('Vendor', $plugin->getVendorNam());
    }

}
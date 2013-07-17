<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;
use Claroline\CoreBundle\Entity\Plugin;

class PluginRepositoryTest extends RepositoryTestCase
{
    public static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Plugin');
        self::createPlugin('Vendor', 'Bundle');
    }

    public function testFindOneByBundleFQCN()
    {
        $plugin = self::$repo->findOneByBundleFQCN('Vendor\Bundle');
        $this->assertEquals('Vendor', $plugin->getVendorName());
    }
}

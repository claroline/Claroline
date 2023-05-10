<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Tests\Repository;

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class PluginRepositoryTest extends RepositoryTestCase
{
    public static $repo;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository(Plugin::class);
        self::createPlugin('Vendor', 'Bundle');
    }

    public function testFindOneByBundleFQCN()
    {
        $plugin = self::$repo->findOneByBundleFQCN('Vendor\Bundle');
        $this->assertEquals('Vendor', $plugin->getVendorName());
    }
}

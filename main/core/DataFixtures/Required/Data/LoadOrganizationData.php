<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

class LoadOrganizationData implements RequiredFixture
{
    private $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->container->get('claroline.manager.organization.organization_manager')->createDefault();
    }
}

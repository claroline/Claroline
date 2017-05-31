<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\PostInstall\Data;

use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * Platform widgets data fixture.
 */
class LoadWorkspaceData implements RequiredFixture
{
    /**
     * Loads the core widgets.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->container->get('claroline.manager.workspace_manager')->getDefaultModel();
        $this->container->get('claroline.manager.workspace_manager')->getDefaultModel(true);
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getOrder()
    {
        return 1;
    }
}

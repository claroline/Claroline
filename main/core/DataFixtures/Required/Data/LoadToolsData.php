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

use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Resource\PwsRightsManagementAccess;
use Claroline\CoreBundle\Persistence\ObjectManager;

class LoadToolsData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $this->updatePersonalWorkspaceResourceRightsConfig($manager);
        $manager->flush();
    }

    private function updatePersonalWorkspaceResourceRightsConfig(ObjectManager $manager)
    {
        $roleUser = $manager->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');
        $config = new PwsRightsManagementAccess();
        $config->setRole($roleUser);
        $config->setIsAccessible(true);
        $manager->persist($config);
        $manager->flush();
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getOrder()
    {
        return 4;
    }
}

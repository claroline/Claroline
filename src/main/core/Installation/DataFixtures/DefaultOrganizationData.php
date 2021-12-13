<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Installation\DataFixtures;

use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\InstallationBundle\Fixtures\PreInstallInterface;
use Claroline\InstallationBundle\Fixtures\PreUpdateInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultOrganizationData extends AbstractFixture implements PreInstallInterface, PreUpdateInterface, ContainerAwareInterface
{
    /** @var OrganizationManager */
    private $organizationManager;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->organizationManager = $container->get('claroline.manager.organization.organization_manager');
    }

    public function load(ObjectManager $manager)
    {
        // create default organization only if it does not exist
        $this->organizationManager->getDefault(true);
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Installation\DataFixtures\Required;

use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultWorkspaceModelsData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var WorkspaceManager */
    private $workspaceManager;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->workspaceManager = $container->get('claroline.manager.workspace_manager');
    }

    public function load(ObjectManager $manager)
    {
        /*$this->workspaceManager->getDefaultModel(false);
        $this->workspaceManager->getDefaultModel(true);*/
    }
}

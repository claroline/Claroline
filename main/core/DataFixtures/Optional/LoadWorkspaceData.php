<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Optional;

use Claroline\CoreBundle\Library\Workspace\Configuration;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadWorkspaceData extends AbstractFixture implements ContainerAwareInterface
{
    private $workspaces;
    private static $codeDiscrCount = 1;

    /**
     * Constructor. Expects an associative array where each key is an unique workspace
     * name and each value is a creator's username. Users must have been loaded
     * and referenced in a previous fixtures with a 'user/[username]' label.
     *
     * For each workspace, a fixture reference will be added with the following label:
     * - workspace/[workspace's name]
     *
     * @param array $workspaces
     */
    public function __construct(array $workspaces)
    {
        $this->workspaces = $workspaces;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $personalWsTemplateFile = $this->container->getParameter('claroline.param.templates_directory').'default.zip';

        foreach ($this->workspaces as $name => $username) {
            $config = new Configuration($personalWsTemplateFile);
            $config->setWorkspaceName($name);
            $config->setWorkspaceCode(substr($name, 0, 1).self::$codeDiscrCount);
            $config->setDisplayable(true);
            $workspace = $workspaceManager->create($config, $this->getReference('user/'.$username));
            $this->setReference("workspace/{$name}", $workspace);
            $wsRoot = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findWorkspaceRoot($workspace);
            $this->setReference('directory/'.$name, $wsRoot);
            ++self::$codeDiscrCount;
        }
    }
}

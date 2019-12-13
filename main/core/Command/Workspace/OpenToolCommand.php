<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Workspace;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Command\AdminCliCommand;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpenToolCommand extends ContainerAwareCommand implements AdminCliCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:open-tool')
            ->setDescription('export workspace archive');
        $this->setDefinition([
            new InputArgument('tool', InputArgument::REQUIRED, 'The tool to open'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        /** @var ObjectManager $om */
        $om = $container->get('doctrine.orm.entity_manager');

        /** @var Tool $tool */
        $tool = $om->getRepository(Tool::class)->findOneBy(['name' => $input->getArgument('tool')]);
        if ($tool) {
            /** @var Workspace[] $workspaces */
            $workspaces = $container->get('doctrine.orm.entity_manager')->getRepository(Workspace::class)->findBy([
                'model' => false,
                'personal' => false,
            ]);

            $output->writeln(sprintf('Opening tool "%s" for %d workspaces...', $tool->getName(), count($workspaces)));

            foreach ($workspaces as $workspace) {
                /** @var OrderedTool $orderedTool */
                $orderedTool = $om->getRepository(OrderedTool::class)->findOneBy([
                    'tool' => $tool,
                    'workspace' => $workspace,
                ]);

                // tool is present in the workspace, check if all role can access its
                if ($orderedTool) {
                    /** @var Role[] $wsRoles */
                    $wsRoles = $workspace->getRoles();
                    foreach ($wsRoles as $role) {
                        // get rights for the current role
                        $toolRights = $om->getRepository(ToolRights::class)
                            ->findBy(['orderedTool' => $orderedTool, 'role' => $role], ['id' => 'ASC']);

                        if (0 < count($toolRights)) {
                            $rights = $toolRights[0];
                        } else {
                            // initialize new rights
                            $rights = new ToolRights();
                            $rights->setRole($role);
                            $rights->setOrderedTool($orderedTool);
                        }

                        $mask = $rights->getMask() ?? 0;
                        if (!($mask & ToolMaskDecoder::$defaultValues['open'])) {
                            // role has no access, grant him
                            $output->writeln(sprintf('Role %s has no access. Give him', $role->getName()));
                            $mask += ToolMaskDecoder::$defaultValues['open'];
                            $rights->setMask($mask);
                            $om->persist($rights);
                        }
                    }
                }
            }
        }

        $om->flush();

        $output->writeln('Done');
    }
}

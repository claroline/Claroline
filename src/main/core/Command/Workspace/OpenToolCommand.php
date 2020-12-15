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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpenToolCommand extends Command implements AdminCliCommand
{
    private $om;
    private $em;

    public function __construct(ObjectManager $om, EntityManagerInterface $em)
    {
        $this->om = $om;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('export workspace archive');
        $this->setDefinition([
            new InputArgument('tool', InputArgument::REQUIRED, 'The tool to open'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Tool $tool */
        $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => $input->getArgument('tool')]);
        if ($tool) {
            /** @var Workspace[] $workspaces */
            $workspaces = $this->em->getRepository(Workspace::class)->findBy([
                'model' => false,
                'personal' => false,
            ]);

            $output->writeln(sprintf('Opening tool "%s" for %d workspaces...', $tool->getName(), count($workspaces)));

            foreach ($workspaces as $workspace) {
                /** @var OrderedTool $orderedTool */
                $orderedTool = $this->om->getRepository(OrderedTool::class)->findOneBy([
                    'tool' => $tool,
                    'workspace' => $workspace,
                ]);

                // tool is present in the workspace, check if all role can access its
                if ($orderedTool) {
                    /** @var Role[] $wsRoles */
                    $wsRoles = $workspace->getRoles();
                    foreach ($wsRoles as $role) {
                        // get rights for the current role
                        $toolRights = $this->om->getRepository(ToolRights::class)
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
                            $this->om->persist($rights);
                        }
                    }
                }
            }
        }

        $this->om->flush();

        $output->writeln('Done');

        return 0;
    }
}

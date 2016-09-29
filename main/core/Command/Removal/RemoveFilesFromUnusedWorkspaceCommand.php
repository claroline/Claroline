<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Removal;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

class RemoveFilesFromUnusedWorkspaceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:clean:files')
            ->setDescription('Remove the unused files from the Claroline storage');

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'When set to true, doesn\'t ask for a confirmation'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<error>Be carreful, you might removes files you don't want to. You should backup your files beforehand to be sure everything still works as intended.</error>");
        $force = $input->getOption('force');
        $container = $this->getContainer();
        $fs = new FileSystem();
        $helper = $this->getHelper('question');

        //Parse the file directory and fetch the other directories
        //We should find Workspaces and Users. Workspaces being formatted like "WORKSPACE_[ID]
        $fileDir = $container->getParameter('claroline.param.files_directory');
        $iterator = new \DirectoryIterator($fileDir);

        foreach ($iterator as $pathinfo) {
            if ($pathinfo->isDir()) {
                $name = $pathinfo->getBasename();

                //look for workspaces
                if (strpos('_'.$name, 'WORKSPACE')) {
                    $parts = explode('_', $name);
                    $id = $parts[1];
                    $workspace = $container->get('claroline.manager.workspace_manager')->getWorkspaceById($id);

                    if (!$workspace) {
                        $continue = false;

                        if (!$force) {
                            $question = new ConfirmationQuestion('Do you really want to remove the directory '.$pathinfo->getPathname().' [y/n] y ', true);
                            $continue = $helper->ask($input, $output, $question);
                        }

                        if ($continue || $force) {
                            $fs->remove($pathinfo->getPathname());
                        }
                    }
                }
            }
        }
    }
}

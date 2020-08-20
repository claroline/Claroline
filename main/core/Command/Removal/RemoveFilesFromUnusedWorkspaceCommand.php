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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

class RemoveFilesFromUnusedWorkspaceCommand extends Command
{
    private $filesDir;
    private $om;

    public function __construct(string $filesDir, ObjectManager $om)
    {
        $this->filesDir = $filesDir;
        $this->om = $om;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Remove the unused files from the Claroline storage');

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
        $fs = new FileSystem();
        $helper = $this->getHelper('question');
        $workspaceRepo = $this->om->getRepository(Workspace::class);
        //Parse the file directory and fetch the other directories
        //We should find Workspaces and Users. Workspaces being formatted like "WORKSPACE_[ID]
        $iterator = new \DirectoryIterator($this->filesDir);

        foreach ($iterator as $pathinfo) {
            if ($pathinfo->isDir()) {
                $name = $pathinfo->getBasename();

                //look for workspaces
                if (strpos('_'.$name, 'WORKSPACE')) {
                    $parts = explode('_', $name);
                    $id = $parts[1];
                    $workspace = $workspaceRepo->find($id);

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

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Solver;
use Composer\Json\JsonFile;
use Composer\Repository\ArrayRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jsonFile = new JsonFile(
            $this->getContainer()->getParameter('kernel.root_dir') . '/../vendor/composer/installed.json'
        );
        $localRepo = new InstalledFilesystemRepository($jsonFile);
        $pool = new Pool();
        $pool->addRepository($localRepo);

        $solver = new Solver(new DefaultPolicy(), $pool, $localRepo);

        $operations = $solver->solve(new Request($pool));

        foreach ($operations as $operation) {
            var_dump($operation->getPackage()->getName());
        }
    }
}

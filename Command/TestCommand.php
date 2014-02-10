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
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\Repository\PlatformRepository;
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
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $installedFile = new JsonFile($rootDir . '/../vendor/composer/installed.json');
        $fromRepo = new InstalledFilesystemRepository($installedFile);
        $toRepo = new ArrayRepository();

        //$fromRepo->addPackage(new Package('behat/mink', '1.5.0.0', '1.5.0'));
        //$fromRepo->addPackage(new Package('behat/mink-browserkit-driver', '1.1.0.0', '1.1.0'));

        $pool = new Pool();
        $pool->addRepository($fromRepo);
        $pool->addRepository(new PlatformRepository());
        $request = new Request($pool);

        foreach ($fromRepo->getPackages() as $package) {
            $request->install($package->getName());
        }

        $solver = new Solver(new DefaultPolicy(), $pool, $toRepo);
        $operations = $solver->solve($request);

        foreach ($operations as $operation) {
            var_dump($operation->getPackage()->getName());
        }
    }
}

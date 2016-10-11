<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DevBundle\Command;

use Claroline\CoreBundle\Command\Traits\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Debug a manager.
 */
class RouterDebugCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = ['route' => 'The route name: '];

    protected function configure()
    {
        $this->setName('claroline:debug:router')->setDescription('Generate a route');
        $this->setDefinition(
            [
                new InputArgument('route', InputArgument::REQUIRED, 'The route'),
                new InputArgument('parameters', InputArgument::IS_ARRAY, 'The method parameters'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $route = $input->getArgument('route');
        $parameters = $input->getArgument('parameters');
        $container = $this->getContainer();
        $output->writeln($container->get('router')->generate($route, $parameters, true));
    }
}

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

use Claroline\AppBundle\Command\BaseCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Debug a manager.
 */
class RouterDebugCommand extends Command
{
    use BaseCommandTrait;

    private $params = ['route' => 'The route name: '];

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate a route');
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
        $output->writeln($this->urlGenerator->generate($route, $parameters));
    }
}

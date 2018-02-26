<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Command;

use Claroline\AppBundle\Routing\ApiRoute;
use Claroline\CoreBundle\Command\Traits\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

class DebugRouterCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    /** @var array */
    private $params = ['class' => 'The class managed by the api'];

    protected function configure()
    {
        $this->setName('claroline:api:router:debug')->setDescription('Shows the api route');
        $this->setDefinition(
            [new InputArgument('class', InputArgument::REQUIRED, 'The class managed by the api.')]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Router */
        $router = $this->getContainer()->get('router');
        /** @var string */
        $class = $input->getArgument('class');
        /** @var RouteCollection */
        $collection = $router->getRouteCollection();
        $describeCollection = new RouteCollection();

        foreach ($collection->getIterator() as $key => $route) {
            if ($route instanceof ApiRoute && $class === $route->getClass()) {
                $describeCollection->add($key, $route);
            }
        }

        $io = new SymfonyStyle($input, $output);
        $helper = new DescriptorHelper();
        $helper->describe($io, $describeCollection, []);
    }
}

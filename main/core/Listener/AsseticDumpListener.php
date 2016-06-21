<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Bazinga\Bundle\JsTranslationBundle\Command\DumpCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @DI\Service()
 */
class AsseticDumpListener
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @DI\Observe("console.command")
     */
    public function onExecute(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        if ($command->getName() === 'assetic:dump') {
            $command = new DumpCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array());
            $output = new ConsoleOutput();
            $resultCode = $command->run($input, $output);
        }
    }
}

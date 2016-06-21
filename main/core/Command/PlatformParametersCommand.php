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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class PlatformParametersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:parameters:set')
            ->setDescription('Set a list of parameters in platform_options.yml.');

        foreach (PlatformConfigurationHandler::$defaultParameters as $param => $value) {
            $this->addOption(
                $param,
                null,
                InputOption::VALUE_REQUIRED,
                'Set a value for the parameter '.$param.'.'
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $handler = $this->getContainer()->get('claroline.config.platform_config_handler');

        foreach ($handler->getDefaultParameters() as $param => $value) {
            if ($value = $input->getOption($param)) {
                $handler->setParameter($param, $value);
            }
        }
    }
}

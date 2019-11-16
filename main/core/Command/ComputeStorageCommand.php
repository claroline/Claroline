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

use Claroline\AppBundle\API\Options;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComputeStorageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:storage:compute')
            ->setDescription('Compute used storage (content of files directory) and store result in platform options');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Computing used storage...');

        $fileManager = $this->getContainer()->get('claroline.manager.file_manager');
        $parametersSerializer = $this->getContainer()->get('Claroline\CoreBundle\API\Serializer\ParametersSerializer');

        $parameters = $parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]);
        $usedStorage = $fileManager->computeUsedStorage();
        $parameters['restrictions']['used_storage'] = $usedStorage;
        $parameters['restrictions']['max_storage_reached'] = isset($parameters['restrictions']['max_storage_size']) &&
            $parameters['restrictions']['max_storage_size'] &&
            $usedStorage >= $parameters['restrictions']['max_storage_size'];
        $parametersSerializer->deserialize($parameters);

        $output->writeln('Used storage computed and saved.');
    }
}

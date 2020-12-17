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
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComputeStorageCommand extends Command
{
    private $fileManager;
    private $parametersSerializer;

    public function __construct(FileManager $fileManager, ParametersSerializer $parametersSerializer)
    {
        $this->fileManager = $fileManager;
        $this->parametersSerializer = $parametersSerializer;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Compute used storage (content of files directory) and store result in platform options');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Computing used storage...');

        $parameters = $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]);
        $usedStorage = $this->fileManager->computeUsedStorage();
        $parameters['restrictions']['used_storage'] = $usedStorage;
        $parameters['restrictions']['max_storage_reached'] = isset($parameters['restrictions']['max_storage_size']) &&
            $parameters['restrictions']['max_storage_size'] &&
            $usedStorage >= $parameters['restrictions']['max_storage_size'];
        $this->parametersSerializer->deserialize($parameters);

        $output->writeln('Used storage computed and saved.');

        return 0;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 8/31/17
 * Time: 4:37 PM.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceMaskDecoderIntegrityCheckerCommand extends Command
{
    private $maskManager;

    public function __construct(MaskManager $maskManager)
    {
        $this->maskManager = $maskManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('claroline:resource_mask_decoder:check')
            ->setDescription('Checks the resource mask decoders integrity of the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $this->maskManager->setLogger($consoleLogger);
        $this->maskManager->checkIntegrity();
    }
}

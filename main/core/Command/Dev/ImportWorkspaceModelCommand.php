<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Dev;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\CoreBundle\Command\AdminCliCommand;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class ImportWorkspaceModelCommand extends ContainerAwareCommand implements AdminCliCommand
{
    use BaseCommandTrait;

    private $params = [
        'path' => 'Absolute path to the archive file: ',
        'code' => 'The new code ',
    ];

    protected function configure()
    {
        $this->setName('claroline:workspace:import_archive')
            ->setDescription('Create a workspace from a zip archive (for debug purpose)');
        $this->setDefinition(
            [
                new InputArgument('path', InputArgument::REQUIRED, 'The absolute path to the zip file.'),
                new InputArgument('code', InputArgument::REQUIRED, 'The owner username'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $destination = @tempnam('claro', '_wscopy');
        $file = $input->getArgument('path');
        copy($file, $destination);
        $file = file_get_contents($file);
        $tmp = @tempnam('claro', '_zip');
        file_put_contents($tmp, $file);
        $file = new File($tmp);

        $object = $this->getContainer()->get('Claroline\AppBundle\API\Crud')->create(
            PublicFile::class,
            [],
            ['file' => $file]
        );

        $zip = new \ZipArchive();
        $zip->open($this->getContainer()->get('Claroline\CoreBundle\Library\Utilities\FileUtilities')->getPath($object));
        $json = $zip->getFromName('workspace.json');
        $zip->close();

        $data = json_decode($json, true);
        $data['code'] = $input->getArgument('code');
        $data['archive'] = $this->getContainer()->get('Claroline\AppBundle\API\SerializerProvider')->serialize($object);
        $workspace = new Workspace();
        $workspace->setCode($data['code']);

        $consoleLogger = ConsoleLogger::get($output);
        $manager = $this->getContainer()->get('claroline.manager.workspace.transfer');
        $manager->setLogger($consoleLogger);
        $manager->create($data, $workspace);
    }
}

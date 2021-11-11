<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Creates an user, optionally with a specific role (default to simple user).
 */
class ImportCommand extends Command
{
    private $crud;
    private $fileUtils;
    private $serializerProvider;
    private $workspaceManager;

    public function __construct(Crud $crud, FileUtilities $fileUtils, SerializerProvider $serializerProvider, WorkspaceManager $workspaceManager)
    {
        $this->crud = $crud;
        $this->fileUtils = $fileUtils;
        $this->serializerProvider = $serializerProvider;
        $this->workspaceManager = $workspaceManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a workspace from a zip archive (for debug purpose)')
            ->setDefinition([
                new InputArgument('path', InputArgument::REQUIRED, 'The absolute path to the zip file.'),
                new InputArgument('code', InputArgument::REQUIRED, 'The new workspace code.'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $destination = tempnam('claro', '_wscopy');
        $file = $input->getArgument('path');
        copy($file, $destination);
        $file = file_get_contents($file);
        $tmp = tempnam('claro', '_zip');
        file_put_contents($tmp, $file);
        $file = new File($tmp);

        $object = $this->crud->create(
            PublicFile::class,
            [],
            ['file' => $file]
        );

        $zip = new \ZipArchive();
        $zip->open($this->fileUtils->getPath($object));
        $json = $zip->getFromName('workspace.json');
        $zip->close();

        $data = json_decode($json, true);
        $data['code'] = $input->getArgument('code');
        $data['archive'] = $this->serializerProvider->serialize($object);
        $workspace = new Workspace();
        $workspace->setCode($data['code']);

        $this->workspaceManager->import($data, $workspace);

        return 0;
    }
}

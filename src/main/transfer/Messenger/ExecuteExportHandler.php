<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TransferBundle\Messenger;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Entity\TransferFileInterface;
use Claroline\TransferBundle\Messenger\Message\ExecuteExport;
use Claroline\TransferBundle\Transfer\ExportProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExecuteExportHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var ExportProvider */
    private $exporter;
    /** @var string */
    private $filesDir;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ExportProvider $exporter,
        string $filesDir
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->exporter = $exporter;
        $this->filesDir = $filesDir;
    }

    public function __invoke(ExecuteExport $exportMessage)
    {
        $exportFile = $this->om->getRepository(ExportFile::class)->find($exportMessage->getExportId());
        if (empty($exportFile)) {
            return;
        }

        // process export
        $this->crud->update($exportFile, [
            'status' => TransferFileInterface::IN_PROGRESS,
        ]);

        try {
            $extra = $exportFile->getExtra() ?? [];
            $options = [];
            if ($exportFile->getWorkspace()) {
                $options[] = Options::WORKSPACE_IMPORT;
                $extra['workspace'] = $this->serializer->serialize($exportFile->getWorkspace(), [Options::SERIALIZE_MINIMAL]);
            }

            $data = $this->exporter->execute(
                $exportFile->getFormat(),
                $exportFile->getAction(),
                $options,
                $extra
            );

            $fs = new FileSystem();
            $fs->dumpFile($this->filesDir.'/transfer'.'/'.$exportFile->getUuid(), $data);

            $status = TransferFileInterface::SUCCESS;
        } catch (\Exception $e) {
            $status = TransferFileInterface::ERROR;
        }

        $this->crud->update($exportFile, [
            'status' => $status,
            'executionDate' => DateNormalizer::normalize(new \DateTime()),
        ]);
    }
}

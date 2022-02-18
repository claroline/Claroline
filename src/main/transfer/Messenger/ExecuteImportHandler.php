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
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Entity\TransferFileInterface;
use Claroline\TransferBundle\Messenger\Message\ExecuteImport;
use Claroline\TransferBundle\Transfer\ImportProvider;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExecuteImportHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var ImportProvider */
    private $importer;
    /** @var FileUtilities */
    private $fileUtilities;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ImportProvider $importer,
        FileUtilities $fileUtilities
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->importer = $importer;
        $this->fileUtilities = $fileUtilities;
    }

    public function __invoke(ExecuteImport $importMessage)
    {
        $importFile = $this->om->getRepository(ImportFile::class)->find($importMessage->getImportId());
        /*if (empty($importFile) || empty($importFile->getFile())) {
            return;
        }*/

        // process import
        $this->crud->update($importFile, [
            'status' => TransferFileInterface::IN_PROGRESS,
        ]);

        try {
            $toImport = $this->fileUtilities->getContents($importFile->getFile());

            $extra = $importFile->getExtra() ?? [];
            $options = [];
            if ($importFile->getWorkspace()) {
                $options[] = Options::WORKSPACE_IMPORT;
                $extra['workspace'] = $this->serializer->serialize($importFile->getWorkspace(), [Options::SERIALIZE_MINIMAL]);
            }

            $data = $this->importer->execute(
                TextNormalizer::sanitize($toImport),
                $importFile->getFormat(),
                $importFile->getAction(),
                $importFile->getUuid(),
                $options,
                $extra
            );

            $status = TransferFileInterface::SUCCESS;
            if (0 !== count($data['data']['error'])) {
                $status = TransferFileInterface::ERROR;
            }
        } catch (\Exception $e) {
            $status = TransferFileInterface::ERROR;
        }

        $this->crud->update($importFile, [
            'status' => $status,
            'executionDate' => DateNormalizer::normalize(new \DateTime()),
        ]);
    }
}

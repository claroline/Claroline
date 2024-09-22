<?php

namespace Claroline\TransferBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\TransferBundle\Entity\AbstractTransferFile;
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Entity\TransferFileInterface;
use Claroline\TransferBundle\Messenger\Message\ExecuteImport;
use Claroline\TransferBundle\Transfer\ImportProvider;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ImportManager
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ImportProvider $importer,
        private readonly FileManager $fileManager,
        private readonly string $logDir
    ) {
    }

    public function getLog(AbstractTransferFile $transferFile): ?string
    {
        $logFile = $this->logDir.DIRECTORY_SEPARATOR.$transferFile->getLog().'.json';
        if (file_exists($logFile)) {
            return file_get_contents($logFile);
        }

        return null;
    }

    public function requestImport(ImportFile $importFile): void
    {
        $importFile->setStatus(TransferFileInterface::IN_PROGRESS);

        $this->om->persist($importFile);
        $this->om->flush();

        // request import execution
        $this->messageBus->dispatch(new ExecuteImport($importFile->getId()), [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]);
    }

    public function import(ImportFile $importFile): string
    {
        try {
            $toImport = $this->fileManager->getContents($importFile->getFile());

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
        ], [Crud::NO_PERMISSIONS]);

        return $status;
    }
}

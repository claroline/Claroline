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
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Entity\TransferFileInterface;
use Claroline\TransferBundle\Messenger\Message\ExecuteExport;
use Claroline\TransferBundle\Messenger\Message\ExecuteImport;
use Claroline\TransferBundle\Transfer\ExportProvider;
use Claroline\TransferBundle\Transfer\ImportProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TransferManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var ExportProvider */
    private $exporter;
    /** @var ImportProvider */
    private $importer;
    /** @var FileManager */
    private $fileManager;
    /** @var string */
    private $filesDir;
    /** @var string */
    private $logDir;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        MessageBusInterface $messageBus,
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ExportProvider $exporter,
        ImportProvider $importer,
        FileManager $fileManager,
        string $filesDir,
        string $logDir
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->messageBus = $messageBus;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->exporter = $exporter;
        $this->importer = $importer;
        $this->fileManager = $fileManager;
        $this->filesDir = $filesDir;
        $this->logDir = $logDir;
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
        $this->messageBus->dispatch(new ExecuteImport($importFile->getId()), [new AuthenticationStamp($this->tokenStorage->getToken()->getUser()->getId())]);
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

    public function requestExport(ExportFile $exportFile): void
    {
        $exportFile->setStatus(TransferFileInterface::IN_PROGRESS);

        $this->om->persist($exportFile);
        $this->om->flush();

        // request export execution
        $this->messageBus->dispatch(new ExecuteExport($exportFile->getId()), [new AuthenticationStamp($this->tokenStorage->getToken()->getUser()->getId())]);
    }

    public function export(ExportFile $exportFile): string
    {
        $fs = new FileSystem();

        if (!$fs->exists($this->filesDir)) {
            $fs->mkdir($this->filesDir);
        }

        $exportPath = $this->filesDir.DIRECTORY_SEPARATOR.$exportFile->getUuid();
        if ($fs->exists($exportPath)) {
            $fs->remove($exportPath);
        }

        try {
            $extra = $exportFile->getExtra() ?? [];
            $options = [];
            if ($exportFile->getWorkspace()) {
                $options[] = Options::WORKSPACE_IMPORT;
                $extra['workspace'] = $this->serializer->serialize($exportFile->getWorkspace(), [Options::SERIALIZE_MINIMAL]);
            }

            $fs->touch($exportPath);

            $this->exporter->execute(
                $exportPath,
                $exportFile->getFormat(),
                $exportFile->getAction(),
                $options,
                $extra
            );

            $status = TransferFileInterface::SUCCESS;
        } catch (\Exception $e) {
            $status = TransferFileInterface::ERROR;
        }

        $this->crud->update($exportFile, [
            'status' => $status,
            'executionDate' => DateNormalizer::normalize(new \DateTime()),
        ], [Crud::NO_PERMISSIONS]);

        return $status;
    }
}

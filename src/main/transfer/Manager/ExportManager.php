<?php

namespace Claroline\TransferBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Entity\TransferFileInterface;
use Claroline\TransferBundle\Messenger\Message\ExecuteExport;
use Claroline\TransferBundle\Transfer\ExportProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExportManager
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ExportProvider $exporter,
        private readonly string $filesDir
    ) {
    }

    public function requestExport(ExportFile $exportFile): void
    {
        $exportFile->setStatus(TransferFileInterface::IN_PROGRESS);

        $this->om->persist($exportFile);
        $this->om->flush();

        // request export execution
        $this->messageBus->dispatch(new ExecuteExport($exportFile->getId()), [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]);
    }

    public function export(ExportFile $exportFile): string
    {
        $fs = new Filesystem();

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

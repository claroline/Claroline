<?php

namespace Claroline\TransferBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\SchedulerBundle\Serializer\ScheduledTaskSerializer;
use Claroline\TransferBundle\Entity\ImportFile;

class ImportFileSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var ScheduledTaskSerializer */
    private $scheduledTaskSerializer;

    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer,
        PublicFileSerializer $fileSerializer,
        WorkspaceSerializer $workspaceSerializer,
        ScheduledTaskSerializer $scheduledTaskSerializer
    ) {
        $this->om = $om;
        $this->userSerializer = $userSerializer;
        $this->fileSerializer = $fileSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->scheduledTaskSerializer = $scheduledTaskSerializer;
    }

    /** @return string */
    public function getClass()
    {
        return ImportFile::class;
    }

    public function getName()
    {
        return 'import_file';
    }

    public function serialize(ImportFile $file, array $options = []): array
    {
        $data = [
            'id' => $file->getUuid(),
            'action' => $file->getAction(),
            'format' => $file->getFormat(),
            'status' => $file->getStatus(),
            'meta' => [
                'createdAt' => DateNormalizer::normalize($file->getCreatedAt()),
                'creator' => $file->getCreator() ? $this->userSerializer->serialize($file->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
            ],
            'executionDate' => DateNormalizer::normalize($file->getExecutionDate()),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $data['extra'] = $file->getExtra();

            // should not be exposed here
            $scheduler = $this->om->getRepository(ScheduledTask::class)->findOneBy(['parentId' => $file->getUuid()]);
            if (!empty($scheduler)) {
                $data['scheduler'] = $this->scheduledTaskSerializer->serialize($scheduler);
            }

            if ($file->getFile()) {
                $data['file'] = $this->fileSerializer->serialize($file->getFile(), [Options::ABSOLUTE_URL]);
            }

            if ($file->getWorkspace()) {
                $data['workspace'] = $this->workspaceSerializer->serialize($file->getWorkspace(), [Options::SERIALIZE_MINIMAL]);
            }
        }

        return $data;
    }

    public function deserialize(array $data, ImportFile $file, array $options = []): ImportFile
    {
        $this->sipe('action', 'setAction', $data, $file);
        $this->sipe('format', 'setFormat', $data, $file);
        $this->sipe('status', 'setStatus', $data, $file);
        $this->sipe('extra', 'setExtra', $data, $file);

        if (isset($data['executionDate'])) {
            $file->setExecutionDate(DateNormalizer::denormalize($data['executionDate']));
        }

        if (isset($data['meta'])) {
            if (isset($data['meta']['createdAt'])) {
                $file->setCreatedAt(DateNormalizer::denormalize($data['meta']['createdAt']));
            }

            if (isset($data['meta']['creator'])) {
                /** @var User $creator */
                $creator = $this->om->getObject($data['meta']['creator'], User::class);
                $file->setCreator($creator);
            }
        }

        if (isset($data['file'])) {
            $uploadedFile = $this->fileSerializer->deserialize($data['file']);
            if ($uploadedFile) {
                $file->setFile($uploadedFile);
            }
        }

        if (array_key_exists('workspace', $data)) {
            $workspace = null;
            if (!empty($data['workspace'])) {
                $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['workspace']['id']]);
            }

            $file->setWorkspace($workspace);
        }

        return $file;
    }
}

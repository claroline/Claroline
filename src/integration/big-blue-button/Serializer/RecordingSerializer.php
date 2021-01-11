<?php

namespace Claroline\BigBlueButtonBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\BigBlueButtonBundle\Entity\Recording;

class RecordingSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return Recording::class;
    }

    public function serialize(Recording $recording, array $options = []): array
    {
        $resourceNode = $recording->getMeeting()->getResourceNode();

        $serialized = [
            'id' => $recording->getUuid(),
            'recordId' => $recording->getRecordId(),
            'startTime' => (int) $recording->getStartTime(),
            'endTime' => (int) $recording->getEndTime(),
            'status' => $recording->getStatus(),
            'participants' => $recording->getParticipants(),
            // this is to display a link to the resource in administration
            // so I don't need much information
            'meeting' => [
                'id' => $resourceNode->getUuid(),
                'slug' => $resourceNode->getSlug(),
                'name' => $resourceNode->getName(),
            ],
            'medias' => $recording->getMedias(),
        ];

        // this is to display a link to the workspace in administration
        // so I don't need much information
        $workspace = $resourceNode->getWorkspace();
        if ($workspace) {
            $serialized['workspace'] = [
                'id' => $workspace->getUuid(),
                'slug' => $workspace->getSlug(),
                'name' => $workspace->getName(),
            ];
        }

        return $serialized;
    }

    public function deserialize(array $data, Recording $recording): Recording
    {
        $this->sipe('recordId', 'setRecordId', $data, $recording);
        $this->sipe('startTime', 'setStartTime', $data, $recording);
        $this->sipe('endTime', 'setEndTime', $data, $recording);
        $this->sipe('status', 'setStatus', $data, $recording);
        $this->sipe('participants', 'setParticipants', $data, $recording);
        $this->sipe('medias', 'setMedias', $data, $recording);

        return $recording;
    }
}

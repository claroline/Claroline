<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\ClacoFormBundle\Entity\EntryUser;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.entry.user")
 * @DI\Tag("claroline.serializer")
 */
class EntryUserSerializer
{
    use SerializerTrait;

    /**
     * Serializes an EntryUser entity for the JSON api.
     *
     * @param EntryUser $entryUser - the entry user to serialize
     * @param array     $options   - a list of serialization options
     *
     * @return array - the serialized representation of the entry user
     */
    public function serialize(EntryUser $entryUser, array $options = [])
    {
        $serialized = [
            'id' => $entryUser->getUuid(),
            'autoId' => $entryUser->getId(),
            'entry' => [
                'id' => $entryUser->getEntry()->getUuid(),
            ],
            'user' => [
                'id' => $entryUser->getUser()->getUuid(),
            ],
            'shared' => $entryUser->isShared(),
            'notifyEdition' => $entryUser->getNotifyEdition(),
            'notifyComment' => $entryUser->getNotifyComment(),
            'notifyVote' => $entryUser->getNotifyVote(),
        ];

        return $serialized;
    }

    /**
     * @param array     $data
     * @param EntryUser $entryUser
     * @param array     $options
     *
     * @return EntryUser
     */
    public function deserialize($data, EntryUser $entryUser, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $entryUser);
        $this->sipe('shared', 'setShared', $data, $entryUser);
        $this->sipe('notifyEdition', 'setNotifyEdition', $data, $entryUser);
        $this->sipe('notifyComment', 'setNotifyComment', $data, $entryUser);
        $this->sipe('notifyVote', 'setNotifyVote', $data, $entryUser);

        return $entryUser;
    }
}

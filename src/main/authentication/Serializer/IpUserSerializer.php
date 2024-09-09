<?php

namespace Claroline\AuthenticationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;

class IpUserSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer
    ) {
    }

    public function getClass(): string
    {
        return IpUser::class;
    }

    public function serialize(IpUser $object): array
    {
        return [
            'id' => $object->getUuid(),
            'ip' => $object->isRange() ? explode(',', $object->getIp()) : $object->getIp(),
            'range' => $object->isRange(),
            'user' => $this->userSerializer->serialize($object->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]),
            'restrictions' => [
                'locked' => $object->isLocked(),
            ],
        ];
    }

    public function deserialize(array $data, IpUser $object, ?array $options = []): IpUser
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $object);
        } else {
            $object->refreshUuid();
        }

        if (isset($data['ip'])) {
            $object->setIp(is_array($data['ip']) ? implode(',', $data['ip']) : $data['ip']);
        }
        $this->sipe('range', 'setRange', $data, $object);
        $this->sipe('restrictions.locked', 'setLocked', $data, $object);

        if (!empty($data['user'])) {
            /** @var User $user */
            $user = $this->om->getRepository(User::class)->findOneBy(['uuid' => $data['user']['id']]);
            if ($user) {
                $object->setUser($user);
            }
        }

        return $object;
    }
}

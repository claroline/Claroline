<?php

namespace Claroline\AuthenticationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;

class IpUserSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer
    ) {
        $this->om = $om;
        $this->userSerializer = $userSerializer;
    }

    public function getClass()
    {
        return IpUser::class;
    }

    public function serialize(IpUser $object): array
    {
        return [
            'id' => $object->getId(),
            'ip' => $object->isRange() ? explode(',', $object->getIp()) : $object->getIp(),
            'range' => $object->isRange(),
            'user' => $this->userSerializer->serialize($object->getUser(), [Options::SERIALIZE_MINIMAL]),
            'restrictions' => [
                'locked' => $object->isLocked(),
            ],
        ];
    }

    public function deserialize(array $data, IpUser $object): IpUser
    {
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

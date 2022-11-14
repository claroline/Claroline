<?php

namespace Claroline\AuthenticationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\OauthUser;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;

class OauthUserSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var UserSerializer */
    private $userSerializer;

    /**
     * OauthUserSerializer constructor.
     */
    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer
    ) {
        $this->om = $om;
        $this->userSerializer = $userSerializer;
    }

    public function getClass()
    {
        return OauthUser::class;
    }

    public function serialize(OauthUser $object, array $options = [])
    {
        return [
            'id' => $object->getId(),
            'service' => $object->getService(),
            'oauthId' => $object->getOauthId(),
            'user' => $this->userSerializer->serialize($object->getUser(), [Options::SERIALIZE_MINIMAL]),
        ];
    }

    public function deserialize(array $data, OauthUser $object, array $options = [])
    {
        $this->sipe('service', 'setService', $data, $object);
        $this->sipe('oauthId', 'setOauthId', $data, $object);

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

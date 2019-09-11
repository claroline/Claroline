<?php

namespace Claroline\CoreBundle\API\Serializer\Cryptography;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Cryptography\ApiToken;
use Claroline\CoreBundle\Entity\User;

class ApiTokenSerializer
{
    use SerializerTrait;

    private $userSerializer;

    /**
     * OptionsSerializer constructor.
     *
     * @param UserSerializer $userSerializer
     */
    public function __construct(UserSerializer $userSerializer, ObjectManager $om)
    {
        $this->userSerializer = $userSerializer;
        $this->om = $om;
    }

    /**
     * @param Options $options
     *
     * @return array
     */
    public function serialize(ApiToken $token, array $options = [])
    {
        return [
            'id' => $token->getUuid(),
            'token' => $token->getToken(),
            'description' => $token->getDescription(),
            'user' => $token->getUser() ? $this->userSerializer->serialize($token->getUser()) : null,
        ];
    }

    /**
     * @param array        $data
     * @param Options|null $options
     *
     * @return Options
     */
    public function deserialize(array $data, ApiToken $token, array $options = [])
    {
        $this->sipe('description', 'setDescription', $data, $token);

        if (isset($data['user'])) {
            $user = $this->om->getRepository(User::class)->findOneByUsername($data['user']['username']);
            $token->setUser($user);
        }

        return $token;
    }

    public function getClass()
    {
        return ApiToken::class;
    }
}

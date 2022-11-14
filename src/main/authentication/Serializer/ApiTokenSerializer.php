<?php

namespace Claroline\AuthenticationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ApiTokenSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
        $this->userSerializer = $userSerializer;
        $this->om = $om;
    }

    public function getName(): string
    {
        return 'api_token';
    }

    public function getClass()
    {
        return ApiToken::class;
    }

    public function serialize(ApiToken $token): array
    {
        return [
            'id' => $token->getUuid(),
            'token' => $token->getToken(),
            'description' => $token->getDescription(),
            'user' => $token->getUser() ? $this->userSerializer->serialize($token->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
            'permissions' => [
                'edit' => $this->authorization->isGranted('EDIT', $token),
                'delete' => $this->authorization->isGranted('DELETE', $token),
            ],
            'restrictions' => [
                'locked' => $token->isLocked(),
            ],
        ];
    }

    public function deserialize(array $data, ApiToken $token): ApiToken
    {
        $this->sipe('id', 'setUuid', $data, $token);
        $this->sipe('token', 'setToken', $data, $token);
        $this->sipe('description', 'setDescription', $data, $token);
        $this->sipe('restrictions.locked', 'setLocked', $data, $token);

        if (isset($data['user'])) {
            /** @var User $user */
            $user = $this->om->getObject($data['user'], User::class, ['id', 'email', 'username']);
            $token->setUser($user);
        }

        return $token;
    }
}

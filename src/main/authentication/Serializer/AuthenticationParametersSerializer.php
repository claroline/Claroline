<?php

namespace Claroline\AuthenticationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;

class AuthenticationParametersSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return AuthenticationParameters::class;
    }

    public function serialize(AuthenticationParameters $authenticationParameters): array
    {
        return [
            'minLength' => $authenticationParameters->getMinLength(),
            'requireLowercase' => $authenticationParameters->getRequireLowercase(),
            'requireUppercase' => $authenticationParameters->getRequireUppercase(),
            'requireSpecialChar' => $authenticationParameters->getRequireSpecialChar(),
            'requireNumber' => $authenticationParameters->getRequireNumber(),
        ];
    }

    public function deserialize(array $data, AuthenticationParameters $authenticationParameters): AuthenticationParameters
    {
        $this->sipe('minLength', 'setMinLength', $data, $authenticationParameters);
        $this->sipe('requireLowercase', 'setRequireLowercase', $data, $authenticationParameters);
        $this->sipe('requireUppercase', 'setRequireUppercase', $data, $authenticationParameters);
        $this->sipe('requireSpecialChar', 'setRequireSpecialChar', $data, $authenticationParameters);
        $this->sipe('requireNumber', 'setRequireNumber', $data, $authenticationParameters);

        return $authenticationParameters;
    }
}

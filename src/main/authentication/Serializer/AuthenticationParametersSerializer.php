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
            'password' => [
                'minLength' => $authenticationParameters->getMinLength(),
                'requireLowercase' => $authenticationParameters->getRequireLowercase(),
                'requireUppercase' => $authenticationParameters->getRequireUppercase(),
                'requireSpecialChar' => $authenticationParameters->getRequireSpecialChar(),
                'requireNumber' => $authenticationParameters->getRequireNumber(),
            ],
            'login' => [
                'helpMessage' => $authenticationParameters->getHelpMessage(),
                'changePassword' => $authenticationParameters->getChangePassword(),
                'internalAccount' => $authenticationParameters->getInternalAccount(),
                'showClientIp' => $authenticationParameters->getShowClientIp(),
            ],
        ];
    }

    public function deserialize(array $data, AuthenticationParameters $authenticationParameters): AuthenticationParameters
    {
        $this->sipe('password.minLength', 'setMinLength', $data, $authenticationParameters);
        $this->sipe('password.requireLowercase', 'setRequireLowercase', $data, $authenticationParameters);
        $this->sipe('password.requireUppercase', 'setRequireUppercase', $data, $authenticationParameters);
        $this->sipe('password.requireSpecialChar', 'setRequireSpecialChar', $data, $authenticationParameters);
        $this->sipe('password.requireNumber', 'setRequireNumber', $data, $authenticationParameters);

        $this->sipe('login.helpMessage', 'setHelpMessage', $data, $authenticationParameters);
        $this->sipe('login.changePassword', 'setChangePassword', $data, $authenticationParameters);
        $this->sipe('login.internalAccount', 'setInternalAccount', $data, $authenticationParameters);
        $this->sipe('login.showClientIp', 'setShowClientIp', $data, $authenticationParameters);

        return $authenticationParameters;
    }
}

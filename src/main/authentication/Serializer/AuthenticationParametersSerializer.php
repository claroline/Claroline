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
                'redirectAfterLoginOption' => $authenticationParameters->getRedirectAfterLoginOption(),
                'redirectAfterLoginUrl' => $authenticationParameters->getRedirectAfterLoginUrl(),
            ],
        ];
    }

    public function deserialize(array $data, AuthenticationParameters $authenticationParameters): AuthenticationParameters
    {
        $passwordData = $data['password'] ?? [];
        $this->sipe('minLength', 'setMinLength', $passwordData, $authenticationParameters);
        $this->sipe('requireLowercase', 'setRequireLowercase', $passwordData, $authenticationParameters);
        $this->sipe('requireUppercase', 'setRequireUppercase', $passwordData, $authenticationParameters);
        $this->sipe('requireSpecialChar', 'setRequireSpecialChar', $passwordData, $authenticationParameters);
        $this->sipe('requireNumber', 'setRequireNumber', $passwordData, $authenticationParameters);

        $loginData = $data['login'] ?? [];
        $this->sipe('helpMessage', 'setHelpMessage', $loginData, $authenticationParameters);
        $this->sipe('changePassword', 'setChangePassword', $loginData, $authenticationParameters);
        $this->sipe('internalAccount', 'setInternalAccount', $loginData, $authenticationParameters);
        $this->sipe('showClientIp', 'setShowClientIp', $loginData, $authenticationParameters);
        $this->sipe('redirectAfterLoginOption', 'setRedirectAfterLoginOption', $loginData, $authenticationParameters);
        $this->sipe('redirectAfterLoginUrl', 'setRedirectAfterLoginUrl', $loginData, $authenticationParameters);

        return $authenticationParameters;
    }
}

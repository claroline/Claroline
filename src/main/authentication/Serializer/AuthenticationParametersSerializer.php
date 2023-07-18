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
            'helpMessage' => $authenticationParameters->getHelpMessage(),
            'changePassword' => $authenticationParameters->getChangePassword(),
            'internalAccount' => $authenticationParameters->getInternalAccount(),
            'showClientIp' => $authenticationParameters->getShowClientIp(),
            'redirectAfterLoginOption' => $authenticationParameters->getRedirectAfterLoginOption(),
            'redirectAfterLoginUrl' => $authenticationParameters->getRedirectAfterLoginUrl(),
        ];
    }

    public function deserialize(array $data, AuthenticationParameters $authenticationParameters): AuthenticationParameters
    {
        $this->sipe('minLength', 'setMinLength', $data, $authenticationParameters);
        $this->sipe('requireLowercase', 'setRequireLowercase', $data, $authenticationParameters);
        $this->sipe('requireUppercase', 'setRequireUppercase', $data, $authenticationParameters);
        $this->sipe('requireSpecialChar', 'setRequireSpecialChar', $data, $authenticationParameters);
        $this->sipe('requireNumber', 'setRequireNumber', $data, $authenticationParameters);
        $this->sipe('helpMessage', 'setHelpMessage', $data, $authenticationParameters);
        $this->sipe('changePassword', 'setChangePassword', $data, $authenticationParameters);
        $this->sipe('internalAccount', 'setInternalAccount', $data, $authenticationParameters);
        $this->sipe('showClientIp', 'setShowClientIp', $data, $authenticationParameters);
        $this->sipe('redirectAfterLoginOption', 'setRedirectAfterLoginOption', $data, $authenticationParameters);
        $this->sipe('redirectAfterLoginUrl', 'setRedirectAfterLoginUrl', $data, $authenticationParameters);

        return $authenticationParameters;
    }
}

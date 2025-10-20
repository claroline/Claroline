<?php

namespace Claroline\AuthenticationBundle\Component\OAuth;

use Claroline\AuthenticationBundle\Entity\OAuthClient;
use League\OAuth2\Client\Provider\GenericProvider;

class GenericOAuth2 extends OAuth2Component
{
    public static function getName(): string
    {
        return 'generic';
    }

    public static function getIcon(): string
    {
        return 'fa fa-earth';
    }

    public function getDefaultMapping(): array
    {
        return [];
    }

    public function getProvider(OAuthClient $client): GenericProvider
    {
        return new GenericProvider([
            'clientId' => $client->getClientId(),
            'clientSecret' => $client->getClientSecret(),
            'urlAuthorize' => $client->getUrlAuthorize(),
            'urlAccessToken' => $client->getUrlAccessToken(),
            'urlResourceOwnerDetails' => $client->getUrlResourceOwnerDetails(),
            'scopes' => $client->getAdditionalParameter('scopes') ?? [],
        ]);
    }
}

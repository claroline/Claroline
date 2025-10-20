<?php

namespace Claroline\GitHubBundle\Component\OAuth;

use Claroline\AuthenticationBundle\Component\OAuth\OAuth2Component;
use Claroline\AuthenticationBundle\Entity\OAuthClient;
use League\OAuth2\Client\Provider\Github;

class GithubOAuth2 extends OAuth2Component
{
    public static function getName(): string
    {
        return 'github';
    }

    public static function getIcon(): string
    {
        return 'fa-brands fa-github';
    }

    public function getDefaultMapping(): array
    {
        return [
            'username' => 'login',
            'name' => 'name',
            'email' => 'email',
        ];
    }

    public function getProvider(OAuthClient $client): Github
    {
        return new Github([
            'clientId' => $client->getClientId(),
            'clientSecret' => $client->getClientSecret(),
        ]);
    }
}

<?php

namespace Claroline\AuthenticationBundle\Component\OAuth;

use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AuthenticationBundle\Entity\OAuthClient;
use League\OAuth2\Client\Provider\AbstractProvider;

interface OAuth2Interface extends ComponentInterface
{
    public static function getIcon(): string;

    public function getProvider(OAuthClient $client): AbstractProvider;

    public function getDefaultMapping(): array;
}

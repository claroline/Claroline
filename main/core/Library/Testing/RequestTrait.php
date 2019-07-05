<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Entity\Cryptography\ApiToken;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\BrowserKit\Client;

trait RequestTrait
{
    public function request($method, $uri, User $user = null, array $parameters = [], $content = null)
    {
        if (!$this->client instanceof Client) {
            throw new \Exception(
                'This method requires a client property of type '.
                'Symfony\Component\BrowserKit\Client'
            );
        }

        if ($user) {
            $om = $this->client->getContainer()->get('claroline.persistence.object_manager');
            $token = $om->getRepository(ApiToken::class)->findOneByUser($user);
            $uri = strpos($uri, '?') ? $uri.'&apitoken='.$token->getToken() : $uri.'?apitoken='.$token->getToken();
        }

        return $this->client->request($method, $uri, $parameters, [], [], $content);
    }
}

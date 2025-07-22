<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

trait RequestTrait
{
    public function request(string $method, string $uri, User $user = null, array $parameters = [], ?string $content = null): Crawler
    {
        if (!$this->client instanceof KernelBrowser) {
            throw new \Exception('This method requires a client property of type Symfony\Bundle\FrameworkBundle\KernelBrowser');
        }

        $server = $user ?
            [
                'PHP_AUTH_USER' => $user->getUsername(),
                'PHP_AUTH_PW' => $user->getPlainPassword(),
            ] :
            [];

        return $this->client->request($method, $uri, $parameters, [], $server, $content);
    }
}

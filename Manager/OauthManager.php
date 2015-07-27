<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\StrictDispatcher;
use FOS\OAuthServerBundle\Entity\ClientManager;
use Claroline\CoreBundle\Entity\Oauth\Client;
use Claroline\CoreBundle\Entity\Oauth\ClarolineAccess;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * @DI\Service("claroline.manager.oauth_manager", parent="fos_oauth_server.client_manager.default")
 */
class OauthManager extends ClientManager
{
    /** @DI\Inject("claroline.manager.curl_manager") */
    public $curlManager;
    /** @DI\Inject("claroline.persistence.object_manager") */
    public $om;

    public function findAllClients()
    {
        return $this->repository->findAll();
    }

    public function connect($host, Client $client)
    {
        $url = $host . '/oauth/v2/token?client_id=' .
            $client->getId() . '_' . $client->getRandomId() . '&client_secret=' .
            $client->getSecret() . '&grant_type=client_credentials';

        $serverOutput = $this->curlManager->exec($url);
        $json = json_decode($serverOutput);

        if (property_exists($json, 'access_token')) {
            $this->createAccess($client, $json->access_token);
            return;
        }

        throw new \Exception('The oauth connection for client ' . $client->getName() . ' could not be initialized');
    }

    /**
     * Only 1 access per client !
     */
    private function createAccess(Client $client, $token)
    {
        //1st step, remove any existing access
        $access = $this->om->getRepository('Claroline\CoreBundle\Entity\Oauth\ClarolineAccess')
            ->findOneByClient($client);
        $this->om->remove($access);
        $this->om->flush();
        //2nd step, creates a new access
        $access = new ClarolineAccess();
        $access->setClient($client);
        $access->setAccessToken($token);
        $this->om->persist($access);
        $this->om->flush();
    }
}

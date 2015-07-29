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
use Claroline\CoreBundle\Entity\Oauth\FriendRequest;
use Claroline\CoreBundle\Entity\Oauth\PendingFriend;
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
    /** @DI\Inject("service_container") */
    public $container;

    public function findAllClients()
    {
        return $this->repository->findAll();
    }

    public function findAllFriendRequests()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\FriendRequest')->findAll();
    }

    public function findAllUnactivatedFriendRequests()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\FriendRequest')->findBy(array('isActivated' => false));
    }

    public function findAllPendingFriends()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\PendingFriend')->findAll();
    }

    public function findFriendRequestByName($name)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\FriendRequest')->findOneBy(array('name' => $name));
    }

    public function connect($host, $id, $secret, FriendRequest $friendRequest = null)
    {
        $url = $host . '/oauth/v2/token?client_id=' .
            $id . '&client_secret=' .
            $secret . '&grant_type=client_credentials';

        $serverOutput = $this->curlManager->exec($url);
        $json = json_decode($serverOutput);

        if (property_exists($json, 'access_token')) {
            return $this->createAccess(
                $id,
                $secret,
                $json->access_token,
                $friendRequest
            );
        }

        throw new \Exception('The oauth connection for id ' . $id . ' could not be initialized');
    }

    public function createFriendRequest(FriendRequest $friend)
    {
        //$host = $this->container->get('request')->getSchemeAndHttpHost();
        $host = $this->container->get('request')->getSchemeAndHttpHost() . $this->container->get('router')->getContext()->getBaseUrl();
        $url = $friend->getHost() . '/admin/oauth/request/friend/name/' . $friend->getName() . '?host=' . urlencode($host);
        $data = $this->curlManager->exec($url);

        if ($data) {
            $this->om->persist($friend);
            $this->om->flush();
        }

        return $url;
    }

    public function removeFriendRequest(FriendRequest $friend)
    {
        $this->om->remove($friend);
        $this->om->flush();
    }

    public function removePendingFriend(PendingFriend $friend)
    {
        $this->om->remove($friend);
        $this->om->flush();
    }

    //@todo basic ip filter
    public function addPendingFriendRequest($name, $host)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        //basic ip protection goes here


        //check the ip match the host aswell

        //then wea ad
        $pending = new PendingFriend();
        $pending->setName($name);
        $pending->setHost($host);
        $pending->setIp($ip);
        $this->om->persist($pending);
        $this->om->flush();
    }

    public function acceptFriendAction(PendingFriend $friend)
    {
        $grantTypes = array(
            'authorization_code',
            'password',
            'refresh_token',
            'token',
            'client_credentials'
        );

        $client = $this->createClient();
        $client->setAllowedGrantTypes($grantTypes);
        $client->setName($friend->getName());
        $this->updateClient($client);
        $url = $friend->getHost() . '/admin/oauth/id/' . $client->getId() . '_' . $client->getRandomId() .
            '/secret/' . $client->getSecret() . '/name/' . $friend->getName();
        $data = $this->curlManager->exec($url);

        if (!json_decode($data)) {
            $this->om->remove($client);
        } else {
            $this->om->remove($friend);
        }

        $this->om->flush();

        return $client;
    }

    /**
     * Only 1 access per client !
     */
    private function createAccess($randomId, $secret, $token, FriendRequest $request = null)
    {
        //1st step, remove any existing access
        $access = $this->om->getRepository('Claroline\CoreBundle\Entity\Oauth\ClarolineAccess')
            ->findOneByRandomId($randomId);
        if ($access) {
            $this->om->remove($access);
            $this->om->flush();
        }
        //2nd step, creates a new access
        $access = new ClarolineAccess();
        $access->setRandomId($randomId);
        $access->setSecret($secret);
        $access->setAccessToken($token);

        if ($request) {
            $request->setIsActivated(true);
            $access->setFriendRequest($request);
            $this->om->persist($request);
        }

        $this->om->persist($access);
        $this->om->flush();

        return $access;
    }
}

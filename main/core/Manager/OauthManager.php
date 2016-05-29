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
use FOS\OAuthServerBundle\Entity\ClientManager;
use Claroline\CoreBundle\Entity\Oauth\Client;
use Claroline\CoreBundle\Entity\Oauth\ClarolineAccess;
use Claroline\CoreBundle\Entity\Oauth\FriendRequest;
use Claroline\CoreBundle\Entity\Oauth\PendingFriend;
use Symfony\Component\Yaml\Yaml;

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

    public function findVisibleClients()
    {
        return $this->repository->findBy(array('isHidden' => false));
    }

    public function findAllFriendRequests()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\FriendRequest')->findAll();
    }

    public function findAllUnactivatedFriendRequests()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\FriendRequest')->findBy(array('isActivated' => false));
    }

    public function findAllActivatedFriendRequests()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\FriendRequest')->findBy(array('isActivated' => true));
    }

    public function findAllPendingFriends()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\PendingFriend')->findAll();
    }

    public function findFriendRequestByName($name)
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\FriendRequest')->findOneBy(array('name' => $name));
    }

    public function findActivatedExternalAuthentications()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Oauth\FriendRequest')->findBy(
            array('isActivated' => true, 'allowAuthentication' => true)
        );
    }

    public function findUsernameClient()
    {
        $clients = $this->findAllClients();

        //because the grant type is an array type, it's stored as a string in the database and we cannot do any query on it
        //let's loop through the different clients then !

        $excludedGrants = ['authorization_code', 'token', 'client_credentials'];
        $authorizedClients = [];

        foreach ($clients as $client) {
            $authorizations = $client->getAllowedGrantTypes();
            $exclude = false;
            foreach ($authorizations as $authorization) {
                if (in_array($authorization, $excludedGrants)) {
                    $exclude = true;
                }
            }

            if (!$exclude && in_array('password', $authorizations)) {
                $authorizedClients[] = $client;
            }
        }

        //if an authorized client has the refresh grant, he has priority. Doesn't matter who he is yet.
        foreach ($authorizedClients as $authorizedClient) {
            if (in_array('refresh', $authorizedClient->getAllowedGrantTypes())) {
                return $authorizedClient;
            }
        }

        return count($authorizedClients) > 0 ? $authorizedClients[0] : null;
    }

    public function connect($host, $id, $secret, FriendRequest $friendRequest)
    {
        $url = $host.'/oauth/v2/token?client_id='.
            $id.'&client_secret='.
            $secret.'&grant_type=client_credentials';

        $serverOutput = $this->curlManager->exec($url);
        $json = json_decode($serverOutput);

        if ($json && property_exists($json, 'access_token')) {
            return $this->createAccess(
                $id,
                $secret,
                $json->access_token,
                $friendRequest
            );
        }

        throw new \Exception('The oauth connection for id '.$id.' could not be initialized');
    }

    public function createFriendRequest(FriendRequest $friend, $master)
    {
        $this->om->persist($friend);
        $this->om->flush();
        $url = $friend->getHost().'/admin/oauth/request/friend/name/'.$friend->getName().'?host='.urlencode($master);
        $data = $this->curlManager->exec($url);

        if (!json_decode($data)) {
            $this->om->remove($friend);
            $this->om->flush();

            throw new Exception\FriendRequestException('An error occured during the friend request', $url);
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
    public function addPendingFriendRequest($name, $host, $autoadd = false)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $pending = new PendingFriend();
        $pending->setName($name);
        $pending->setHost($host);
        $pending->setIp($ip);
        $this->om->persist($pending);
        $this->om->flush();

        if ($autoadd) {
            $client = $this->acceptFriendAction($pending, true);
            $this->hideClient($client);
        }
    }

    public function acceptFriendAction(PendingFriend $friend, $hide = false)
    {
        $grantTypes = array(
            'authorization_code',
            'password',
            'refresh_token',
            'token',
            'client_credentials',
        );

        $client = $this->createClient();
        $client->setAllowedGrantTypes($grantTypes);
        $client->setName($friend->getName());
        $client->setRedirectUris(array($friend->getHost().'/oauth/v2/log/'.$friend->getName()));
        $this->updateClient($client);
        $url = $friend->getHost().'/admin/oauth/id/'.$client->getId().'_'.$client->getRandomId().
            '/secret/'.$client->getSecret().'/name/'.$friend->getName();
        $data = $this->curlManager->exec($url);

        if (!json_decode($data)) {
            $this->om->remove($client);
            $this->om->remove($friend);
            $this->om->flush();
            throw new \Exception('The friend request host was not found for the url '.$url);
        } else {
            $this->om->remove($friend);
            $this->om->flush();
        }

        return $client;
    }

    /**
     * Only 1 access per client !
     */
    private function createAccess($randomId, $secret, $token, FriendRequest $request)
    {
        //1st step, remove any existing access
        $access = $this->om->getRepository('Claroline\CoreBundle\Entity\Oauth\ClarolineAccess')
            ->findOneByRandomId($randomId);

        if ($access === null) {
            $access = new ClarolineAccess();
        }

        $access->setRandomId($randomId);
        $access->setSecret($secret);
        $access->setAccessToken($token);
        $request->setIsActivated(true);
        $access->setFriendRequest($request);
        $this->om->persist($request);
        $this->om->persist($access);
        $this->om->flush();

        return $access;
    }

    public function hideClient(Client $client)
    {
        $client->hide();
        $this->om->persist($client);
        $this->om->flush();
    }

    public function isAutoCreated($host)
    {
        $file = $this->container->getParameter('claroline.param.oauth_master_platforms');

        if (file_exists($file)) {
            $data = Yaml::parse($file);

            foreach ($data as $authorization) {
                if ($authorization === $host) {
                    return true;
                }
                if (ip2long($authorization) === ip2long($_SERVER['REMOTE_ADDR'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function updateFriend(FriendRequest $request)
    {
        $this->om->persist($request);
        $this->om->flush();
    }
}

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
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Oauth\Client;

/**
 * @DI\Service("claroline.manager.api_manager")
 */
class ApiManager
{

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "oauthManager" = @DI\Inject("claroline.manager.oauth_manager"),
     *     "curlManager"  = @DI\Inject("claroline.manager.curl_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        OauthManager $oauthManager,
        CurlManager $curlManager
    )
    {
        $this->om = $om;
        $this->oauthManager = $oauthManager;
        $this->curlManager = $curlManager;
    }

    public function url($host, $url, Client $client, $payload = null, $type = 'GET')
    {
        $access = $this->om->getRepository('Claroline\CoreBundle\Entity\Oauth\ClarolineAccess')
            ->findOneByClient($client);
        if (!$access) $this->oauthManager->connect($host, $client);
        $url = $host . '/' . $url . '?access_token=' . $access->getAccessToken();
        $serverOutput = $this->curlManager->exec($url, $type);
        $json = json_decode($serverOutput);

        if ($json) {
            if ($json->error === 'access_denied') {
                $this->oauthManager->connect($host, $client);
                $this->url($host, $url, $client, $payload, $type);
            }
        }

        return $serverOutput;
    }
}

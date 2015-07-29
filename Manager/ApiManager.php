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
use Symfony\Component\Form\Form;
use Symfony\Component\Form\AbstractType;

/**
 * @DI\Service("claroline.manager.api_manager")
 * This service allows 2 instances of claroline-connect to communicate through their REST api.
 * The REST api requires an oauth authentication (wich is why the $id/$secret combination is required)
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

    public function url($host, $url, $id, $secret, $payload = null, $type = 'GET')
    {
        $access = $this->om->getRepository('Claroline\CoreBundle\Entity\Oauth\ClarolineAccess')
            ->findOneByRandomId($id);
        if (!$access) $this->oauthManager->connect($host, $id, $secret);
        $url = $host . '/' . $url . '?access_token=' . $access->getAccessToken();
        $serverOutput = $this->curlManager->exec($url, $payload, $type);
        $json = json_decode($serverOutput);

        if ($json) {
            if (property_exists($json, 'error')) {
                if ($json->error === 'access_denied' || $json->error = 'invalid_grant') {
                    $this->oauthManager->connect($host, $id, $secret);
                    $this->url($host, $url, $id, $secret, $payload, $type);
                }
            }
        }

        return $serverOutput;
    }

    public function formEncode($entity, Form $form, AbstractType $formType)
    {
        $baseName = $formType->getName();
        $payload = array();

        foreach ($form->getIterator() as $el) {
            if (is_array($entity)) {
                $payload[$baseName . '[' . $el->getName() . ']'] = $entity[$el->getName()];
            }
        }

        return $payload;
    }
}

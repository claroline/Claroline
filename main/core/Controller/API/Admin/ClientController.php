<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Admin;

use JMS\DiExtraBundle\Annotation as DI;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Manager\OauthManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ClientController extends FOSRestController
{
    private $oauthManager;

	 /**
    * @DI\InjectParams({
    *     "oauthManager" = @DI\Inject("claroline.manager.oauth_manager")
    * })
    */
   public function _construct(OauthManager $oauthManager)
   {
       $this->oauthManager = $oauthManager;
   }

    /**
     * Get the client id and the client secret.
     * @Route("/idsecret.{_format}", name="claro_id_secret", defaults={"_format":"json"})
     */
    public function getIdsecretAction()
    {
        $arr = $this->oauthManager->findVisibleClients();
        $client = $arr[0];
        $clientId = $client->getConcatRandomId();
        $clientSecret = $client->getSecret();

        return ['client_id' => $clientId, 'client_secret' => $clientSecret];
    }

        /**
         * Check if access token is expired.
         * @Route("/expired.{_format}", name="claro_token_expired", defaults={"_format":"json"})
         */
        public function getExpiredAction()
        {
            $arr = $this->oauthManager->findVisibleClients();
            $client = $arr[0];
            $tab = $client->getAccessTokens(); // all access tokens
            $mostRecentToken = $tab[count($tab) - 1];

            return ['hasExpired' => $mostRecentToken->hasExpired()];
        }
}

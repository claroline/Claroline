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

use Claroline\CoreBundle\Manager\OauthManager;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
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
     *
     * @Route("/client/public", name="claro_id_secret", defaults={"_format":"json"})
     */
    public function getIdsecretAction()
    {
        return $this->oauthManager->findUsernameClient();
    }

    /**
     * Check if access token is expired.
     * Note from ngodfraind: I'm not sure this is correct because the most recent token might not be the one you want you want to check
     * but I'd need to test it.
     *
     * @Route("/client/expired", name="claro_token_expired", defaults={"_format":"json"})
     */
    public function getExpiredAction()
    {
        $client = $this->oauthManager->findUsernameClient();
        $tab = $client->getAccessTokens(); // all access tokens
        $mostRecentToken = $tab[count($tab) - 1];

        return ['hasExpired' => $mostRecentToken->hasExpired()];
    }
}

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




/**
 * @NamePrefix("client_")
 */
class ClientController extends FOSRestController {

	 private $oauthManager;

	/**
    * @DI\InjectParams({
    *     "oauthManager" = @DI\Inject("claroline.manager.oauth_manager")
    * })
    */
   public function _construct(OauthManager $oauthManager){
     $this->oauthManager = $oauthManager;
   }


   /**
    * Get the client id and the client secret
    */
    public function getIdsecretAction(){
      $arr = $this->oauthManager->findVisibleClients();
      $client = $arr[0];
      $clientId = $client->getConcatRandomId();
      $clientSecret = $client->getSecret();

      $result = array('client_id' => $clientId, 'client_secret' =>$clientSecret);
      return $result;

    }

		/**
		 * Check if access token is expired
		 */
		public function getExpiredAction(){
			$arr = $this->oauthManager->findVisibleClients();
      $client = $arr[0];
			$tab = $client->getAccessTokens(); // all access tokens
			$mostRecentToken = $tab[count($tab)-1];

			$result = array("hasExpired"=>$mostRecentToken->hasExpired());

			return $result;


		}


}

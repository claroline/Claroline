<?php
namespace Icap\DropzoneBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Entity\User;
use Icap\DropzoneBundle\Entity\Dropzone;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.manager.dropzone_manager")
 */

class DropzoneManager
{
    private $container;
    private $maskManager;

        /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     * 		"maskManager" = @DI\Inject("claroline.manager.mask_manager")
     * })
     */
    public function __construct($container,MaskManager $maskManager)
    {
        $this->container = $container;
        $this->maskManager = $maskManager;
    }


    /**
     *  Getting the user that have the 'open' rights.
     *  Excluded the admin profil.
     *  @return array UserIds.
     * */
    public function getDropzoneUsersIds(Dropzone $dropzone)
    {
    	
    	//getting the ressource node
    	$ressourceNode = $dropzone->getResourceNode();
    	// getting the rights of the ressource node
    	$rights = $ressourceNode->getRights();

    	// will contain the 'authorized to open' user's ids.
    	$userIds = array();
    	$test = array();
    	// searching for roles with the 'open' right
    	foreach ($rights as $ressourceRight) {
    		$role = $ressourceRight->getRole();
    		$mask = $ressourceRight->getMask();

    		$decodedRights = $this->maskManager->decodeMask($mask,$ressourceNode->getResourceType());
    		if(array_key_exists('open', $decodedRights) && $decodedRights['open'] == true && $role->getName() != 'ROLE_ADMIN')
    		{
    			// the role has the 'open' right
    			array_push($test,$role->getName());
    			$users = $role->getUsers();
    			foreach ($users as $user) {
    				array_push($userIds, $user->getId());
    			}
    			
    		}
    	}
    	$userIds = array_unique($userIds);
    	/*var_dump($userIds);
    	var_dump($test);
    	die;
		*/
    	return $userIds;
    }

}
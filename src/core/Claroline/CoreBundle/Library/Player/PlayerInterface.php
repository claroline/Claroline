<?php

namespace Claroline\CoreBundle\Library\Player;

/*
 * implements method wich will be used by the Claroline\CoreBunde\Controller\ResourceController
 * when "playing" a file.
 */
interface PlayerInterface
{
    /*
     * will be called by the OpenAction() method from the ResourceController.
     * It should render the called player
     */
    public function indexAction($workspaceId);
    
    /*
     * returns the mime type of the files played
     */
    public function getMimeType();
    
    /*
     * returns the played name
     */
    public function getPlayerName();
}
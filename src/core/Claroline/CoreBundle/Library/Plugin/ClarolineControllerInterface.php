<?php
namespace Claroline\CoreBundle\Library\Plugin;

interface ClarolineControllerInterface
{
    public function viewAction($id);
    public function addToDirectoryAction($id);  
    public function deleteAction($id); 
}
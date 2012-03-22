<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\ChooseResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Form\DirectoryType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Plugin\ClarolineControllerInterface;

class DirectoryController extends Controller implements ClarolineControllerInterface
{
    public function viewAction($id)
    {
        $formResource = $this->get('form.factory')->create(new ChooseResourceType(), new ResourceType());
        $currentDirectory = $this->get('claroline.resource.manager')->find($id);
        
        $resources = $this->get('claroline.resource.manager')->getChildrenById($id);
        
        return $this->render(
            'ClarolineCoreBundle:Directory:view.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources, 'id' => $id, 'currentDirectory' => $currentDirectory )
        );
    }
    
    public function addToDirectoryAction($id)
    {
        $directoryManager =  $this->get('claroline.directory.manager');
        $formDir = $directoryManager ->getDirectoryForm();
        
        return $this->render(
            'ClarolineCoreBundle:Directory:index.html.twig', array('form_directory' => $formDir->createView(), 'id' => $id)
        );
    }
    
    public function addAction($id)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new DirectoryType());
        $form->bindRequest($request);

        if ($form->isValid())
        {
             $directoryManager = $this->get('claroline.directory.manager');
             $directoryName = $form['name']->getData();
             $user = $this->get('security.context')->getToken()->getUser();
             $directoryManager->addDirectory($directoryName, $user, $id);
             
             if(null != $this->get('claroline.common.history_browser')->getLastContext())
             {        
                 return $this->redirect($this->get('claroline.common.history_browser')->getLastContext()->getUri());
             }
             else
             {
                 $route = $this->get('router')->generate('claro_resource_index');
                 return new RedirectResponse($route);
             }
        }
    }    
    
    public function deleteAction($id)
    {
        $directoryManager = $this->get('claroline.directory.manager');  
        $directoryManager->deleteById($id);
        $url = $this->generateUrl('claro_resource_index');

        return $this->redirect($url);
    }
}
<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\Response; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

use Innova\PathBundle\Entity\Path;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceActivity;


class PathController extends Controller 
{
    /**
     * @Route(
     *     "/",
     *     name = "innova_path_from_desktop",
     *     options = {"expose"=true}
     * )
     *
     * @Template("InnovaPathBundle::path_desktop.html.twig")
     */
    public function fromDesktopAction()
    {
        return array();
    }

    /**
     * @Route(
     *     "/innova_path_deploy",
     *     name = "innova_path_deploy"
     * )
     * @Method("POST")
     * @Template("InnovaPathBundle::path_workspace.html.twig")
     */
    public function deployAction()
    {
        $manager = $this->entityManager();

        // Récupération vars HTTP
        $pathId = $this->get('request')->request->get('path-id');
        $path = $manager->getRepository('InnovaPathBundle:Path')->findOneById($pathId);
        
        // JSON string to Object - Récupération des childrens de la racine
        $json = json_decode($path->getPath());
        $json_root_steps = $json->steps;

        // Récupération Workspace courant.
        $workspaceId = $this->get('request')->request->get('workspace-id');
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        // Récupération utilisateur courant.
        $user = $this->get('security.context')->getToken()->getUser();
  
        $this->JSONParser($json_root_steps, $user, $workspace, null, 2);

        return array('workspace' => $workspace);
    }

    private function JSONParser($steps, $user, $workspace, $parent, $lvl)
    {
        $manager= $this->entityManager();

        foreach ($steps as $step) {
            echo $step->name."<br/>";

            // Récupération de l'objet resourceType activity. Utile pour le ResourceNode
            $resourceType = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneById(5);

            // Création ResourceNode
            $resourceNode = new ResourceNode();
            $resourceNode->setName($step->name);
            $resourceNode->setClass("Claroline\CoreBundle\Entity\Resource\Activity");
            $resourceNode->setCreator($user);
            $resourceNode->setResourceType($resourceType);
            $resourceNode->setWorkspace($workspace);
            $resourceNode->setParent($parent);
            $resourceNode->setMimeType("custom/activity");
            /*
            setIcon($icon)
            setNext(ResourceNode $next = null, $setPrev = false)
            setPrevious(ResourceNode $previous = null, $setNext = false)
            */
            $manager->persist($resourceNode);
            
            // Création Activité
            $activity = new Activity();
            $activity->setName($step->name);
            $activity->setInstructions("Consigne");
            $activity->setResourceNode($resourceNode);

            $manager->persist($activity);
            $manager->flush();

            // Gestion de la jointure ResourceActivity
            $resourceActivity = new ResourceActivity();
            $resourceActivity->setActivity($activity);
            $resourceActivity->setResourceNode($resourceNode);
            $resourceActivities = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
                ->findByActivity($activity->getId());
            $count = count($resourceActivities)+1; // TODO: A revoir
            $resourceActivity->setSequenceOrder($count);

            $manager->persist($resourceActivity);
            $manager->flush();   

            // récursivité sur les enfants possibles.
            $this->JSONParser($step->children, $user, $workspace, $resourceNode, $lvl+1);
        }

        $manager->flush();     
    }

    /**
     * @Route(
     *     "/",
     *     name = "innova_path_from_workspace",
     *     options = {"expose"=true}
     * )
     *
     * @Template("InnovaPathBundle::path_workspace.html.twig")
     *
     */
    public function fromWorkspaceAction()
    {
        $id = $this->get('request')->query->get('id');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($id);    

        $paths = array();
        $manager= $this->entityManager();
        $results = $manager->getRepository('InnovaPathBundle:Path')->findByWorkspace($id);

        foreach ($results as $result) {
            $path = new \stdClass();
            $path->id = $result->getId();
            $path->user = $result->getUser();
            $path->path = $result->getPath();
            $paths[] = $path;
        }

        return array('workspace' => $workspace, 'paths' => $paths);
    }

    /**
     * @Route(
     *     "/paths",
     *     name = "innova_path_get_paths",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     */
    public function getPathsAction()
    {
        $em = $this->entityManager();
        
        $results = $em->getRepository('InnovaPathBundle:Path')->findAll();

        $paths = array();

        foreach ($results as $result) {
            $path = new \stdClass();
            $path->id = $result->getId();
            $path->path = json_decode($result->getPath());

            $paths[] = $path;
        }

        return new JsonResponse($paths);
    }

    /**
     * @Route(
     *     "/path/{id}",
     *     name = "innova_path_get_path",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     */
    public function getPathAction(Path $path)
    {
        $newPath = json_decode($path->getPath());
        $newPath->id = $path->getId();
    
        return new JsonResponse($newPath);
    }

    /**
    * @Route(
    *     "/path/add",
    *     name = "innova_path_add_path",
    *     options = {"expose"=true}
    * )
    * @Method("POST")
    *
    */
    public function addPathAction()
    {

        $em = $this->entityManager();

        $editDate = new \DateTime();
        $user = "Arnaud";
        $content = $this->get('request')->getContent();
        
        $new_path = New Path;
        $new_path->setUser($user)
                 ->setEditDate($editDate)
                 ->setPath($content);

        $em->persist($new_path);
        $em->flush();

        return New Response(
            $new_path->getId()
        );
    }

    /**
    * @Route(
    *     "/path/edit/{id}",
    *     name = "innova_path_edit_path",
    *     options = {"expose"=true}
    * )
    * @Method("PUT")
    *
    */
    public function editPathAction(Path $path)
    {
        $em = $this->entityManager();

        $editDate = new \DateTime();
        $content = $this->get('request')->getContent();

        $path->setEditDate($editDate)
             ->setPath($content);

        $em->persist($path);
        $em->flush();

        return New Response(
            $path->getId()
        );
    }

    /**
    * @Route(
    *     "/path/delete/{id}",
    *     name = "innova_path_delete_path",
    *     options = {"expose"=true}
    * )
    * @Method("DELETE")
    *
    */
    public function deletePathAction(Path $path)
    {
        $em = $this->entityManager();

        $em->remove($path);
        $em->flush();

        return New Response("ok");
    }

    public function entityManager()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $em = $this->getDoctrine()->getManager();
        
        return $em;
    }

}
     

     
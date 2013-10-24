<?php

/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2013 Innovalangues
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   Entity
 * @package    InnovaPathBundle
 * @subpackage PathBundle
 * @author     Innovalangues <contact@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    0.1
 * @link       http://innovalangues.net
 */
namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Resource;
use Innova\PathBundle\Entity\StepType;
use Innova\PathBundle\Entity\StepWho;
use Innova\PathBundle\Entity\StepWhere;
use Innova\PathBundle\Entity\Step2ResourceNode;
use Innova\PathBundle\Entity\NonDigitalResource;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Security\TokenUpdater;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class PathController
 *
 * @category   Controller
 * @package    Innova
 * @subpackage PathBundle
 * @author     Innovalangues <contact@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    0.1
 * @link       http://innovalangues.net
*/
class PathController extends Controller
{
    /**
     * fromDesktopAction function
     * @return array response
     *
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
     * deployAction function
     * @return array workspace / OK
     *
     * @Route(
     *     "/innova_path_deploy",
     *     name = "innova_path_deploy"
     * )
     * @Method("POST")
     * @Template("InnovaPathBundle::path_workspace.html.twig")
     */
    public function deployAction()
    {
        $pathManager = $this->container->get('innova.manager.path_manager');
        try {
            $isDeployed = $pathManager->deploy();
            if ($isDeployed) {
                // Delete success
                $this->container->get('session')->getFlashBag()->add(
                    'success',
                    'Selected path is successfully deployed'
                );
            }
            else {
                // Delete error
                $this->container->get('session')->getFlashBag()->add(
                    'error',
                    'Selected path can\'t be deployed due to a technical problem. Please try again.'
                );
            }
        } catch (Exception $e) {
            // User is not authorized to delete current path
            // or Path to delete is not found
            $this->container->get('session')->getFlashBag()->add(
                'error',
                $e->getMessage()
            );
        }
        return $isDeployed;
    }

    

    /**
     * fromWorkspaceAction function
     * @return array workspace / paths
     *
     * @Route(
     *     "/",
     *     name = "innova_path_from_workspace"
     * )
     * @Template("InnovaPathBundle::path_workspace.html.twig")
     */
    public function fromWorkspaceAction()
    {
        $pathManager = $this->container->get('innova.manager.path_manager');

        $manager = $this->container->get('doctrine.orm.entity_manager');
        $id = $this->get('request')->query->get('id');
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($id);

        $paths = $pathManager->findAllFromWorkspace($workspace);

        return array('workspace' => $workspace, 'paths' => $paths);
    }

    /**
     * @Route(
     *      "workspace/{workspaceId}/path/editor/{pathId}",
     *      name = "innova_path_editor",
     *      defaults={"pathId"= null},
     *      options = {"expose"=true}
     * )
     * @Template("InnovaPathBundle:Editor:main.html.twig")
     */
    public function editorAction($workspaceId, $pathId = null)
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');

        $currentUser = $this->get('security.context')->getToken()->getUser();
        $pathCreator = "";

        if ($pathId != null) {
            $pathCreator = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($pathId)->getCreator();
        }

        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        if($currentUser == $pathCreator || $pathId == null) {
            return array('workspace' => $workspace, 'pathId' => $pathId);
        }
        else{
            echo "non non non";
            die();
        }
    }

    /**
     * @Route(
     *      "workspace/{workspaceId}/path/player/{pathId}",
     *      name = "innova_path_player",
     *      defaults={"pathId"= null},
     *      options = {"expose"=true}
     * )
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function PlayerAction($workspaceId, $pathId = null)
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        return array('workspace' => $workspace);
    }

    /**
     * @Route(
     *     "/paths",
     *     name = "innova_path_get_paths",
     *     options = {"expose"=true}
     * )
     * @Method("GET")
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
     * getPathAction function
     * @param string $id
     * @return JsonResponse
     *
     * @Route(
     *     "/path/{id}",
     *     name = "innova_path_get_path",
     *     options = {"expose"=true}
     * )
     * @Method("GET")
     */
    public function getPathAction($id)
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');
        $path = $manager->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($id);

        if ($path) {
            $newPath = json_decode($path->getPath());
            $newPath->id = $path->getId();

            return new JsonResponse($newPath);
        }
        else {
            // Path not found
            throw $this->createNotFoundException('The path does not exist');
        }
    }

    /**
     * addPathAction function
     * @return Response($new_path->getId())
     *
     * @Route(
     *     "/path/add",
     *     name = "innova_path_add_path",
     *     options = {"expose"=true}
     * )
     * @Method("POST")
     */
    public function addPathAction()
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');

        // Récupération utilisateur courant.
        $user = $this->get('security.context')->getToken()->getUser();
        $workspaceId = $this->get('request')->request->get('workspaceId');
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        // création du dossier _paths s'il existe pas.
        if (!$pathsDirectory = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneByName("_paths")) {
            $pathsDirectory = new ResourceNode();
            $pathsDirectory->setName("_paths");
            $pathsDirectory->setClass("Claroline\CoreBundle\Entity\Resource\Directory");
            $pathsDirectory->setCreator($user);
            $pathsDirectory->setResourceType($manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneById(2));
            $root = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
            $pathsDirectory->setWorkspace($workspace);
            $pathsDirectory->setParent($root);
            $pathsDirectory->setMimeType("custom/directory");
            $pathsDirectory->setIcon($manager->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(7));

            $manager->persist($pathsDirectory);
            $manager->flush();
        }

        $resourceNode = new ResourceNode();
        $resourceNode->setClass("Innova\PathBundle\Entity\Path");
        $resourceNode->setCreator($user);
        $resourceNode->setResourceType($manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("path"));
        $resourceNode->setWorkspace($workspace);
        $resourceNode->setParent($pathsDirectory);
        $resourceNode->setMimeType("");
        $resourceNode->setIcon($manager->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(1));
        $resourceNode->setName('Path');

        $pathName = $this->get('request')->request->get('pathName');
        $content = $this->get('request')->request->get('path');

        $new_path = new Path;
        $new_path->setPath($content);
        $resourceNode->setName($pathName);
        $new_path->setResourceNode($resourceNode);
        $new_path->setDeployed(false);
        $new_path->setModified(false);
        $manager->persist($resourceNode);
        $manager->persist($new_path);
        $manager->flush();

        return new Response(
            $resourceNode->getId()
        );
    }

    /**
     * editPathAction function
     * @param string $path path of activity
     * @return Response
     *
     * @Route(
     *     "/path/edit/{id}",
     *     name = "innova_path_edit_path",
     *     options = {"expose"=true}
     * )
     * @Method("PUT")
     */
    public function editAction($id)
    {
        $pathManager = $this->container->get('innova.manager.path_manager');
        $pathId = $pathManager->edit();

        return new Response($pathId);
    }

    /**
     * showPathAction function
     * @param string $path path of activity
     *
     * @Route(
     *     "/path/show",
     *     name = "innova_path_play",
     *     options = {"expose"=true}
     * )
     * @Method("POST")
     */
    public function showPathAction()
    {
        $em = $this->entityManager();
        $pathId = $this->get('request')->request->get('pathId');
        $workspaceId = $this->get('request')->request->get('workspaceId');
        $path = $em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);
        $stepId = $em->getRepository('InnovaPathBundle:Step')->findOneBy(array('path' => $path, 'parent' => null))->getId();

        $url = $this->generateUrl('innova_step_show', array('workspaceId' => $workspaceId, 'pathId' => $pathId, 'stepId' => $stepId));
        return $this->redirect($url);
    }

    /**
     * Check if path name is unique for current user and current workspace
     * @Route(
     *      "/path/check_name",
     *      name = "innova_path_check_unique_name",
     *      options = {"expose" = true}
     * )
     * @Method("POST")
     */
    public function checkNameIsUniqueAction()
    {
        
    }
    
    /**
     * Delete path from database
     * @return RedirectResponse
     * 
     * @Route(
     *     "/path/delete",
     *     name = "innova_path_delete_path",
     *     options = {"expose"=true}
     * )
     * @Method("DELETE")
     */
    public function deletePathAction()
    {
        $pathManager = $this->container->get('innova.manager.path_manager');
        try {
            $isDeleted = $pathManager->delete();
            if ($isDeleted) {
                // Delete success
                $this->container->get('session')->getFlashBag()->add(
                    'success',
                    'Selected path is successfully deleted'
                );
            }
            else {
                // Delete error
                $this->container->get('session')->getFlashBag()->add(
                    'error',
                    'Selected path can\'t be deleted due to a technical problem. Please try again.'
                );
            }
        } catch (Exception $e) {
            // User is not authorized to delete current path
            // or Path to delete is not found
            $this->container->get('session')->getFlashBag()->add(
                'error',
                $e->getMessage()
            );
        }
        
        // Redirect to path list
        $workspaceId = $this->container->get('request')->request->get('workspaceId');
        $url = $this->container->get('router')->generate('claro_workspace_open_tool', array ('workspaceId' => $workspaceId, 'toolName' => 'innova_path'));
        return new RedirectResponse($url, 302);
    }


    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->container->get('security.context')->isGranted($attributes, $object)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * entityManager function
     * @return $em
     *
     */
    public function entityManager()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        return $em;
    }

    /**
     * resourceManager function
     *
     * @return $rm
     *
     */
    public function resourceManager()
    {
        $rm = $this->get('claroline.manager.resource_manager');
        return $rm;
    }
}
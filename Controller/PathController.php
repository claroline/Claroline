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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

// Controller dependencies
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Innova\PathBundle\Manager\PathManager;

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
 * 
 * @Route(
 *      "",
 *      name = "innova_path",
 *      service="innova.path.controller"
 * )
 */
class PathController
{
    /**
     * Current entity manager for data persist
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    
    /**
     * Current session
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;
    
    /**
     * Current security context
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;
    
    /**
     * Router manager
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;
    
    /**
     * Translation manager
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;
    
    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    
    /**
     * Current path manager
     * @var \Innopva\PathBundle\Manager\PathManager;
     */
    protected $pathManager;
    
    /**
     * Class constructor
     * Inject needed dependencies
     * @param EntityManagerInterface   $entityManager
     * @param SessionInterface         $session
     * @param SecurityContextInterface $securityContext
     * @param RouterInterface          $router
     * @param TranslatorInterface      $translator
     * @param PathManager              $pathManager
     */
    public function __construct(
	    EntityManagerInterface   $entityManager,
        SessionInterface         $session,
        SecurityContextInterface $securityContext,
        RouterInterface          $router,
        TranslatorInterface      $translator,
        PathManager              $pathManager
    )
    {
        $this->entityManager   = $entityManager;
        $this->session         = $session;
        $this->securityContext = $securityContext;
        $this->router          = $router;
        $this->translator      = $translator;
        $this->pathManager     = $pathManager;
    }
    
    /**
     * Inject current request into service
     * @param Request $request
     * @return \Innova\PathBundle\Controller\PathController
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
        
        return $this;
    }
    
    
    /**
     * Default action when tool is opened from workspace
     * Displays list of available paths for current worspace
     * @return array workspace / paths
     *
     * @Route(
     *     "/",
     *     name = "innova_path_from_workspace"
     * )
     * @Method("GET")
     * @Template("InnovaPathBundle::path_workspace.html.twig")
     */
    public function fromWorkspaceAction()
    {
        $id = $this->request->query->get('id');
        $workspace = $this->entityManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($id);
    
        $paths = $this->pathManager->findAllFromWorkspace($workspace);
    
        return array (
            'workspace' => $workspace, 
            'paths' => $paths,
        );
    }

    /**
     * showPathAction function
     * @param string $path path of activity
     * @return RedirectResponse
     *
     * @Route(
     *     "/path/show",
     *     name = "innova_path_play",
     *     options = {"expose"=true}
     * )
     * @Method("POST")
     */
    public function showAction()
    {
        $pathId = $this->request->get('pathId');
        $workspaceId = $this->request->get('workspaceId');
        $path = $this->entityManager->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);
        $stepId = $this->entityManager->getRepository('InnovaPathBundle:Step')->findOneBy(array('path' => $path, 'parent' => null))->getId();
    
        $url = $this->router->generate('innova_step_show', array(
            'workspaceId' => $workspaceId, 
            'pathId' => $pathId, 
            'stepId' => $stepId,
        ));
    
        return new RedirectResponse($url, 302);
    }
    
    /**
     * Create a new path
     * @return Response
     *
     * @Route(
     *     "/path/add",
     *     name = "innova_path_add_path",
     *     options = {"expose"=true}
     * )
     * @Method("POST")
     */
    public function addAction()
    {
        $resourceNode = $this->pathManager->create();
    
        return new Response($resourceNode->getId());
    }
    
    /**
     * Edit an existing path
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
        $pathId = $this->pathManager->edit();
    
        return new Response($pathId);
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
    public function deleteAction()
    {
        try {
            $isDeleted = $this->pathManager->delete();
            if ($isDeleted) {
                // Delete success
                $this->session->getFlashBag()->add(
                    'success',
                    $this->translator->trans("path_delete_success", array(), "innova_tools")
                );
            }
            else {
                // Delete error
                $this->session->getFlashBag()->add(
                    'error',
                    $this->translator->trans("path_delete_error", array(), "innova_tools")
                );
            }
        } catch (Exception $e) {
            // User is not authorized to delete current path
            // or Path to delete is not found
            $this->session->getFlashBag()->add(
                'error',
                $e->getMessage()
            );
        }
    
        // Redirect to path list
        $workspaceId = $this->request->get('workspaceId');
        $url = $this->router->generate('claro_workspace_open_tool', array ('workspaceId' => $workspaceId, 'toolName' => 'innova_path'));
    
        return new RedirectResponse($url, 302);
    }
    
    /**
     * Display path editor wizard
     * @return array|RedirectResponse
     * 
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
        $currentUser = $this->securityContext->getToken()->getUser();
        $pathCreator = "";

        if (!empty($pathId)) {
            $pathCreator = $this->entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($pathId)->getCreator();
        }

        $workspace = $this->entityManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        if ($currentUser == $pathCreator || empty($pathId)) {
            // Current user is allowed to edit this path
            return array (
                'workspace' => $workspace, 
                'pathId' => $pathId,
            );
        }
        else {
            // Current user not allowed to acces editor for this path
            $url = $url = $this->router->generate('claro_workspace_open_tool', array (
                'workspaceId' => $workspaceId, 
                'toolName' => 'innova_path',
            ));
            
            return new RedirectResponse($url, 302);
        }
    }

    /**
     * Display playable path
     * @return array
     * 
     * @Route(
     *      "workspace/{workspaceId}/path/player/{pathId}",
     *      name = "innova_path_player",
     *      defaults={"pathId"= null},
     *      options = {"expose"=true}
     * )
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function playerAction($workspaceId, $pathId = null)
    {
        $workspace = $this->entityManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        return array (
            'workspace' => $workspace,
        );
    }

    /**
     * getPathAction function
     * @param string $id
     * @return JsonResponse
     * @throws NotFoundHttpException
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
        $path = $this->entityManager->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($id);

        if ($path) {
            $newPath = json_decode($path->getPath());
            $newPath->id = $path->getId();

            return new JsonResponse($newPath);
        }
        else {
            // Path not found
            throw new NotFoundHttpException($this->translator->trans("path_not_found", array(), "innova_tools"));
        }
    }
    
    /**
     * Deploy path
     * Create all needed resources for path to be played
     * @return RedirectResponse
     *
     * @Route(
     *     "/innova_path_deploy",
     *     name = "innova_path_deploy"
     * )
     * @Method("POST")
     */
    public function deployAction()
    {
        try {
            $isDeployed = $this->pathManager->deploy();
            if ($isDeployed) {
                // Deploy success
                $this->session->getFlashBag()->add(
                    'success',
                    $this->translator->trans("deploy_success", array(), "innova_tools")
                );
            }
            else {
                // Deploy error
                $this->session->getFlashBag()->add(
                    'error',
                    $this->translator->trans("deploy_error", array(), "innova_tools")
                );
            }
        } catch (Exception $e) {
            // Exception trows during deployement
            $this->session->getFlashBag()->add(
                'error',
                $e->getMessage()
            );
        }
    
        // Redirect to path list
        $workspaceId = $this->request->get('workspaceId');
        $url = $this->router->generate('claro_workspace_open_tool', array ('workspaceId' => $workspaceId, 'toolName' => 'innova_path'));
        
        return new RedirectResponse($url, 302);
    }
    
    /**
     * Check if path name is unique for current user and current workspace
     * @return JsonResponse
     * 
     * @Route(
     *      "/path/check_name",
     *      name = "innova_path_check_unique_name",
     *      options = {"expose" = true}
     * )
     * @Method("POST")
     */
    public function checkNameIsUniqueAction()
    {
        $isUnique = $this->pathManager->checkNameIsUnique($this->request->get('pathName'));
        
        return new JsonResponse($isUnique);
    }
}
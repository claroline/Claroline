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
 * @package    Innova
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

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

// Controller dependencies
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Innova\PathBundle\Manager\PathManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

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
 *      service="innova_path.controller.path"
 * )
 */
class PathController
{
    /**
     * Current session
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;
    
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
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;
    
    /**
     * Class constructor
     * Inject needed dependencies
     * @param SessionInterface         $session
     * @param RouterInterface          $router
     * @param TranslatorInterface      $translator
     * @param PathManager              $pathManager
     */
    public function __construct(
        SessionInterface         $session,
        RouterInterface          $router,
        TranslatorInterface      $translator,
        PathManager              $pathManager
    )
    {
        $this->session         = $session;
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
}
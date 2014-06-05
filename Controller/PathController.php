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

use Symfony\Component\HttpFoundation\RedirectResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

// Controller dependencies
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Manager\PublishmentManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Innova\PathBundle\Entity\Path\Path;

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
 *      "workspaces/{workspaceId}/tool/path",
 *      name    = "innova_path",
 *      service = "innova_path.controller.path"
 * )
 * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"mapping": {"workspaceId": "id"}})
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
     * Current path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;
    
    /**
     * Current publishment manager
     * @var \Innova\PathBundle\Manager\PublishmentManager
     */
    protected $publishmentManager;
    
    /**
     * Class constructor
     * Inject needed dependencies
     * @param SessionInterface         $session
     * @param RouterInterface          $router
     * @param TranslatorInterface      $translator
     * @param PathManager              $pathManager
     * @param PublishmentManager       $publishmentManager
     */
    public function __construct(
        SessionInterface         $session,
        RouterInterface          $router,
        TranslatorInterface      $translator,
        PathManager              $pathManager,
        PublishmentManager       $publishmentManager)
    {
        $this->session     = $session;
        $this->router      = $router;
        $this->translator  = $translator;
        $this->pathManager = $pathManager;
        $this->publishmentManager = $publishmentManager;
    }
    
    /**
     * Delete path from database
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Innova\PathBundle\Entity\Path\Path $path
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route(
     *     "/delete/{id}",
     *     name         = "innova_path_delete",
     *     requirements = {"id" = "\d+"},
     *     options      = {"expose"=true}
     * )
     * @Method("DELETE")
     */
    public function deleteAction(AbstractWorkspace $workspace, Path $path)
    {
        try {
            $this->pathManager->delete($path);
            
            // Delete success
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans("path_delete_success", array(), "innova_tools")
            );
        } catch (\Exception $e) {
            // Error
            $this->session->getFlashBag()->add(
                'error',
                $e->getMessage()
            );
        }
    
        // Redirect to path list
        $url = $this->router->generate('claro_workspace_open_tool', array (
            'workspaceId' => $workspace->getId(), 
            'toolName' => 'innova_path'
        ));
    
        return new RedirectResponse($url, 302);
    }
    
    /**
     * Publish path
     * Create all needed resources for path to be played
     * 
     * @Route(
     *     "/publish/{id}",
     *     name         = "innova_path_publish",
     *     requirements = {"id" = "\d+"},
     *     options      = {"expose" = true}
     * )
     * @Method("GET")
     */
    public function publishAction(AbstractWorkspace $workspace, Path $path)
    {
        try {
            $this->publishmentManager->publish($path);
        
            // Publish success
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('publish_success', array(), "innova_tools")
            );
        } catch (\Exception $e) {
            // Error
            $this->session->getFlashBag()->add(
                'error',
                $e->getMessage()
            );
        }
        
        // Redirect to path list
        $url = $this->router->generate('claro_workspace_open_tool', array (
            'workspaceId' => $workspace->getId(), 
            'toolName' => 'innova_path'
        ));
        
        return new RedirectResponse($url, 302);
    }
}
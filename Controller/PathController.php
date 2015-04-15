<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

// Controller dependencies
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Manager\PublishingManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
 *      "/",
 *      name    = "innova_path",
 *      service = "innova_path.controller.path"
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
     * Current path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * Publishing manager
     * @var \Innova\PathBundle\Manager\PublishingManager
     */
    protected $publishingManager;

    /**
     * Class constructor
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Symfony\Component\Routing\RouterInterface                 $router
     * @param \Symfony\Component\Translation\TranslatorInterface         $translator
     * @param \Innova\PathBundle\Manager\PathManager                     $pathManager
     * @param \Innova\PathBundle\Manager\PublishingManager               $publishingManager
     */
    public function __construct(
        SessionInterface         $session,
        RouterInterface          $router,
        TranslatorInterface      $translator,
        PathManager              $pathManager,
        PublishingManager        $publishingManager)
    {
        $this->session           = $session;
        $this->router            = $router;
        $this->translator        = $translator;
        $this->pathManager       = $pathManager;
        $this->publishingManager = $publishingManager;
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
    public function publishAction(Workspace $workspace, Path $path)
    {
        $this->pathManager->checkAccess('EDIT', $path);

        try {
            $this->publishingManager->publish($path);

            // Publish success
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('publish_success', array(), 'innova_tools')
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
            'workspaceId' => $path->getWorkspace()->getId(),
            'toolName' => 'innova_path'
        ));
        
        return new RedirectResponse($url, 302);
    }
}

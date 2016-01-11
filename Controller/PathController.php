<?php

namespace Innova\PathBundle\Controller;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\PathWidgetConfig;
use Innova\PathBundle\Form\Type\PathWidgetConfigType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

// Controller dependencies
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Manager\PublishingManager;
use Innova\PathBundle\Entity\Path\Path;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * Current Entity Manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

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
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     * @param \Innova\PathBundle\Manager\PublishingManager $publishingManager
     */
    public function __construct(
        ObjectManager                 $om,
        FormFactoryInterface          $formFactory,
        AuthorizationCheckerInterface $authorizationChecker,
        PathManager                   $pathManager,
        PublishingManager             $publishingManager)
    {
        $this->om                   = $om;
        $this->formFactory          = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->pathManager          = $pathManager;
        $this->publishingManager    = $publishingManager;
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
     * @Method("PUT")
     */
    public function publishAction(Path $path)
    {
        $this->pathManager->checkAccess('EDIT', $path);

        $response = array ();
        try {
            $this->publishingManager->publish($path);

            // Publish success
            $response['status']   = 'OK';
            $response['messages'] = array ();
            $response['data']     = json_decode($path->getStructure()); // Send updated data
        } catch (\Exception $e) {
            // Error
            $response['status']   = 'ERROR';
            $response['messages'] = array( $e->getMessage() );
            $response['data']     = null;
        }
        
        return new JsonResponse($response);
    }

    /**
     * @param WidgetInstance $widgetInstance
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return array
     *
     * @Route(
     *      "/widget/config/{widgetInstance}",
     *      name= "innova_path_widget_config"
     * )
     * @Method("POST")
     * @Template("InnovaPathBundle:Widget:config.html.twig")
     */
    public function updateWidgetAction(WidgetInstance $widgetInstance, Request $request)
    {
        // User can not edit the Widget
        if (!$this->authorizationChecker->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        $config = $this->pathManager->getWidgetConfig($widgetInstance);
        if (null === $config) {
            $config = new PathWidgetConfig();
            $config->setWidgetInstance($widgetInstance);
        }

        $form   = $this->formFactory->create(new PathWidgetConfigType(), $config);

        $form->bind($request);
        if ($form->isValid()) {
            $this->om->persist($config);
            $this->om->flush();

            return new Response('success', 204);
        }

        return array (
            'form'     => $form->createView(),
            'instance' => $widgetInstance,
        );
    }
}

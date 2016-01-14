<?php

namespace Innova\PathBundle\Controller;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\TagBundle\Manager\TagManager;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

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
     * @var \Claroline\TagBundle\Manager\TagManager
     */
    protected $tagManager;

    /**
     * Class constructor
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     * @param \Innova\PathBundle\Manager\PublishingManager $publishingManager
     * @param \Claroline\TagBundle\Manager\TagManager $tagManager
     */
    public function __construct(
        ObjectManager                 $om,
        FormFactoryInterface          $formFactory,
        SessionInterface              $session,
        TranslatorInterface           $translator,
        AuthorizationCheckerInterface $authorizationChecker,
        PathManager                   $pathManager,
        PublishingManager             $publishingManager,
        TagManager                    $tagManager)
    {
        $this->om                   = $om;
        $this->formFactory          = $formFactory;
        $this->session              = $session;
        $this->translator           = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->pathManager          = $pathManager;
        $this->publishingManager    = $publishingManager;
        $this->tagManager           = $tagManager;
    }
    
    /**
     * Publish path
     * Create all needed resources for path to be played
     * 
     * @Route(
     *     "/publish/{id}/{redirect}",
     *     name         = "innova_path_publish",
     *     requirements = {"id" = "\d+"},
     *     options      = {"expose" = true}
     * )
     * @Method({"GET", "PUT"})
     */
    public function publishAction(Path $path, $redirect = false, Request $request)
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

        if ($redirect) {
            // That's not an AJAX call, so display a flash message and redirect the User
            $message = ('OK' === $response['status']) ? 'publish_success' : 'publish_error';
            $this->session->getFlashBag()->add(
                ( 'OK' === $response['status'] ? 'success' : 'error' ),
                $this->translator->trans($message, array(), 'path_wizards')
            );

            return new RedirectResponse($request->headers->get('referer'));
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

        $form = $this->formFactory->create(new PathWidgetConfigType(), $config);

        $form->bind($request);
        if ($form->isValid()) {
            // Manage tags
            $tags = $form->get('tags')->getData();
            if (!empty($tags)) {
                $tags = explode(',', $tags);
                $uniqueTags = array ();
                foreach ($tags as $tag) {
                    $value = trim($tag);
                    if (!empty($value)) {
                        $uniqueTags[strtolower($value)] = $value;
                    }
                }

                foreach ($uniqueTags as $tagName) {
                    $tagObject = $this->tagManager->getOnePlatformTagByName($tagName);
                    if (!empty($tagObject)) {
                        $config->addTag($tagObject);
                    }
                }
            }

            $this->om->persist($config);
            $this->om->flush();

            return new Response('success', 204);
        }

        return array (
            'form'     => $form->createView(),
            'instance' => $widgetInstance,
            'tags'     => $this->tagManager->getPlatformTags(),
        );
    }
}

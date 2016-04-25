<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
// Controller dependencies
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Manager\PublishingManager;
use Innova\PathBundle\Entity\Path\Path;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PathController.
 *
 * @category   Controller
 *
 * @author     Innovalangues <contact@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * @version    0.1
 *
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
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * Current path manager.
     *
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * Publishing manager.
     *
     * @var \Innova\PathBundle\Manager\PublishingManager
     */
    protected $publishingManager;

    /**
     * Class constructor.
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Symfony\Component\Translation\TranslatorInterface         $translator
     * @param \Innova\PathBundle\Manager\PathManager                     $pathManager
     * @param \Innova\PathBundle\Manager\PublishingManager               $publishingManager
     */
    public function __construct(
        SessionInterface              $session,
        TranslatorInterface           $translator,
        PathManager                   $pathManager,
        PublishingManager             $publishingManager)
    {
        $this->session = $session;
        $this->translator = $translator;
        $this->pathManager = $pathManager;
        $this->publishingManager = $publishingManager;
    }

    /**
     * Publish path
     * Create all needed resources for path to be played.
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

        $response = array();
        try {
            $this->publishingManager->publish($path);

            // Publish success
            $response['status'] = 'OK';
            $response['messages'] = array();
            $response['data'] = json_decode($path->getStructure()); // Send updated data
        } catch (\Exception $e) {
            // Error
            $response['status'] = 'ERROR';
            $response['messages'] = array($e->getMessage());
            $response['data'] = null;
        }

        if ($redirect) {
            // That's not an AJAX call, so display a flash message and redirect the User
            $message = ('OK' === $response['status']) ? 'publish_success' : 'publish_error';
            $this->session->getFlashBag()->add(
                ('OK' === $response['status'] ? 'success' : 'error'),
                $this->translator->trans($message, array(), 'path_wizards')
            );

            return new RedirectResponse($request->headers->get('referer'));
        }

        return new JsonResponse($response);
    }
}

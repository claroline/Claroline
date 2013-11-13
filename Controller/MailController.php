<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\MailManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 *
 * Controller of the user's desktop.
 */
class MailController extends Controller
{
    private $formFactory;
    private $request;
    private $mailManager;
    private $router;
    private $sc;
    private $ch;

    /**
     * @DI\InjectParams({
     *     "formFactory" = @DI\Inject("claroline.form.factory"),
     *     "request"     = @DI\Inject("request"),
     *     "mailManager" = @DI\Inject("claroline.manager.mail_manager"),
     *     "router"      = @DI\Inject("router"),
     *     "sc"          = @DI\Inject("security.context"),
     *     "ch"          = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        MailManager $mailManager,
        RouterInterface $router,
        SecurityContextInterface $sc,
        PlatformConfigurationHandler $ch
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->mailManager = $mailManager;
        $this->router = $router;
        $this->sc = $sc;
        $this->ch = $ch;
    }

    /**
     * @EXT\Route(
     *     "/form/{userId}",
     *     name="claro_mail_form"
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Displays the mail form.
     *
     * @param integer $userId
     *
     * @return Response
     */
    public function formAction(User $user)
    {
        return array(
            'form' => $this->formFactory->create(FormFactory::TYPE_EMAIL)->createView(),
            'userId' => $user->getId()
        );
    }

    /**
     * @EXT\Route(
     *     "/send/{userId}",
     *     name="claro_mail_send"
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Mail:form.html.twig")
     *
     * Handles the mail form submission (sends a mail).
     *
     * @param User $user
     *
     * @return Response
     */
    public function sendAction(User $user)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_EMAIL);
        $form->handleRequest($this->request);
        $sender = $this->sc->getToken()->getUser();

        if ($form->isValid()) {
            $data = $form->getData();
            $body = $data['content'];
            $body .= '<hr> ' . $sender->getUsername() . ' (' . $sender->getMail() .
                ') - ClarolineConnect - ' . $this->ch->getParameter('name');
            $this->mailManager->send($data['object'], $body, array($user));
        }

        return new RedirectResponse($this->router->generate('claro_profile_view', array('userId' => $user->getId())));
    }
}

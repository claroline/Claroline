<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 *
 * Controller of the user's desktop.
 */
class MailController extends Controller
{
    private $formFactory;

    /**
     * @DI\InjectParams({
     *     "formFactory" = @DI\Inject("claroline.form.factory"),
     *     "request"     = @DI\Inject("request"),
     *     "mailer"      = @DI\Inject("mailer"),
     *     "router"      = @DI\Inject("router")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        \Swift_Mailer $mailer,
        RouterInterface $router
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->mailer = $mailer;
        $this->router = $router;
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

        if ($form->isValid()) {
            $data = $form->getData();
            $message = \Swift_Message::newInstance()
                ->setSubject($data['object'])
                ->setFrom('noreply@claroline.net')
                ->setTo($user->getMail())
                ->setBody($data['content'], 'text/html');
            $this->mailer->send($message);
        }

        return new RedirectResponse($this->router->generate('claro_profile_view', array('userId' => $user->getId())));
    }
}

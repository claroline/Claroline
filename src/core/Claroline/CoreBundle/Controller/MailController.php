<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class MailController extends Controller
{
    private $formFactory;

    /**
     * @DI\InjectParams({
     *     "formFactory" = @DI\Inject("claroline.form.factory"),
     *     "request"     = @DI\Inject("request"),
     *     "mailer"      = @DI\Inject("mailer")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        \Swift_Mailer $mailer
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->mailer = $mailer;
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
            'form' => $this->formFactory->create(FormFactory::TYPE_MAIL)->createView(),
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
        $form = $this->formFactory->create(FormFactory::TYPE_MAIL);
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

        // add success/error message...
        return array(
            'form' => $form->createView(),
            'userId' => $user->getId()
        );
    }
}

<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\MailType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class MailController extends Controller
{
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
        $form = $this->createForm(new MailType());

        return array(
            'form' => $form->createView(),
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
     * @EXT\Template()
     *
     * Handles the mail form submission (sends a mail).
     *
     * @param integer $userId
     *
     * @return Response
     */
    public function sendAction(User $user)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new MailType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $message = \Swift_Message::newInstance()
                ->setSubject($data['object'])
                ->setFrom('noreply@claroline.net')
                ->setTo($user->getMail())
                ->setBody($data['content'], 'text/html');
            $this->get('mailer')->send($message);
        }

        // add success/error message...

        return array(
            'form' => $form->createView(),
            'userId' => $user->getId()
        );
    }
}

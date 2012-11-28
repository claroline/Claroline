<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\MailType;
use Symfony\Component\HttpFoundation\Response;

class MailController extends Controller
{
    public function formAction($userId)
    {
        $form = $this->createForm(new MailType());

        return $this->render(
            'ClarolineCoreBundle:Mail:mail_form.html.twig',
            array('form' => $form->createView(), 'userId' => $userId)
        );
    }

    public function sendAction($userId)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new MailType());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $user = $this->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\User')->find($userId);
            $message = \Swift_Message::newInstance()
                ->setSubject($data['object'])
                ->setFrom('noreply@claroline.net')
                ->setTo($user->getMail())
                ->setBody($data['content']);
            $this->get('mailer')->send($message);
        }

        return new Response(
            "rhello"
        );
    }
}

<?php

namespace Claroline\ForumBundle\Controller;


use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\ForumBundle\Entity\Widget\LastMessageWidgetConfig;
use Claroline\ForumBundle\Form\Widget\LastMessageWidgetConfigType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class WidgetController extends Controller
{
    /**
     * @Route("/widget/listmessages/{id}/config", name="claroline_forum_last_message_widget_configure", requirements={"id" = "\d+"})
     * @Method("POST")
     */
    public function updateWidgetBlogList(Request $request, WidgetInstance $widgetInstance)
    {
        if (!$this->get('security.authorization_checker')->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        $lastMessageWidgetConfig = $this->get("claroline.manager.forum_widget")->getConfig($widgetInstance);

        if ($lastMessageWidgetConfig === null) {
            echo 'ça passe ';
            $lastMessageWidgetConfig = new LastMessageWidgetConfig();
            $lastMessageWidgetConfig->setWidgetInstance($widgetInstance);
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(new LastMessageWidgetConfigType(), $lastMessageWidgetConfig);
        $form->submit($request);

        var_dump(get_class($form->getData()));
        var_dump($form->getData()->getDisplayMyLastMessages());
        var_dump($form->isValid());
        if ($form->isValid()) {
            $entityManager = $this->get('doctrine.orm.entity_manager');

            $entityManager->persist($lastMessageWidgetConfig);
            $entityManager->flush();

            return new Response('', Response::HTTP_NO_CONTENT);
        }
        else {

//            var_dump($form->getData());
            var_dump($form->isSubmitted());
//            foreach($form->getErrors() as $error) {
//
//                echo '<pre>';
//                var_dump($error);
//                echo '</pre>';
//            }

        }

        return $this->render(
            'ClarolineForumBundle:Widget:lastMessageWidgetConfig.html.twig',
            array(
                'form' => $form->createView(),
                'widgetInstance' => $widgetInstance
            )
        );
    }
}
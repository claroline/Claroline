<?php

namespace Claroline\CoreBundle\Controller\Widget;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class SimpleTextController extends Controller
{
    /**
     * @EXT\Route(
     *     "/simple_text_update/config/{widget}",
     *     name="claro_simple_text_update_config"
     * )
     * @EXT\Method("POST")
     */
    public function updateSimpleTextWidgetConfig(WidgetInstance $widget)
    {
        if (!$this->get('security.context')->isGranted('edit', $widget)) {
            throw new AccessDeniedException();
        }

       $simpleTextConfig = $this->get('claroline.manager.simple_text_manager')->getTextConfig($widget);
       $form = $this->get('claroline.form.factory')->create(FormFactory::TYPE_SIMPLE_TEXT);
       $form->bind($this->getRequest());

       if ($form->isValid()) {
           $formDatas = $form->get('content')->getData();
           $content = is_null($formDatas) ? '' : $formDatas;

           if ($simpleTextConfig) {
               $simpleTextConfig->setContent($content);
           } else {
               $simpleTextConfig = new SimpleTextConfig();
               $simpleTextConfig->setWidgetInstance($widget);
               $simpleTextConfig->setContent($content);
           }
       } else {
           return $$this->render(
               'ClarolineCoreBundle:Widget:config_simple_text_form.html.twig',
               array(
                   'form' => $form->createView(),
                   'config' => $widget
               )
           );
       }

       $em = $this->get('doctrine.orm.entity_manager');
       $em->persist($simpleTextConfig);
       $em->flush();

       return new Response('success', 204);
    }
}

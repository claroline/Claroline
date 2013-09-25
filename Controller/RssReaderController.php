<?php

namespace Claroline\RssReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\RssReaderBundle\Form\ConfigType;
use Claroline\RssReaderBundle\Entity\Config;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

class RssReaderController extends Controller
{
    /**
     * @EXT\Route(
     *     "/simple_text_update/config/{widget}",
     *     name="claro_rss_config_update"
     * )
     * @EXT\Method("POST")
     */
    public function updateSimpleTextWidgetConfig(WidgetInstance $widget)
    {
        //vérification d'accès ici
       $rssConfig = $this->get('claroline.manager.rss_manager')->getConfig($widget);
       $form = $this->container->get('form.factory')->create(new ConfigType, new Config());
       $form->bind($this->getRequest());
       
       if ($rssConfig) {
          if ($form->isValid()) {
            $rssConfig->setUrl($form->get('url')->getData());
          }
       } else {
           if ($form->isValid()) {
               $rssConfig = new Config();
               $rssConfig->setWidgetInstance($widget);
               $rssConfig->setUrl($form->get('url')->getData());
           }
       }
       
       $em = $this->get('doctrine.orm.entity_manager');
       $em->persist($rssConfig);
       $em->flush();
       
       return new RedirectResponse($this->get('claroline.manager.widget_manager')->getRedirectRoute($widget));
    }
}
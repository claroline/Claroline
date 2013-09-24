<?php

namespace Claroline\CoreBundle\Controller\Widget;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;

class SimpleTextController extends Controller
{
    /**
     * @EXT\Route(
     *     "/simple_text_update/config/{config}",
     *     name="claro_simple_text_update_config"
     * )
     * @EXT\Method("POST")
     */
    public function updateLogWorkspaceWidgetConfig(DisplayConfig $config)
    {
        //vérification d'accès ici
        
       $simpleTextConfig = $this->get('claroline.manager.simple_text_manager')->getTextConfig($config);
       $form = $this->get('claroline.form.factory')->create(FormFactory::TYPE_SIMPLE_TEXT);
       $form->bind($this->getRequest());
       
       if ($simpleTextConfig) {
          if ($form->isValid()) {
            $simpleTextConfig->setContent($form->get('content')->getData());
          }
       } else {
           if ($form->isValid()) {
               $simpleTextConfig = new SimpleTextConfig();
               $simpleTextConfig->setDisplayConfig($config);
               $simpleTextConfig->setContent($form->get('content')->getData());
           }
       }
       
       $em = $this->get('doctrine.orm.entity_manager');
       $em->persist($simpleTextConfig);
       $em->flush();
       
       //redirection
    }
}
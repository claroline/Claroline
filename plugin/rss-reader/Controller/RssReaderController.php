<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
        if (!$this->get('security.authorization_checker')->isGranted('edit', $widget)) {
            throw new AccessDeniedException();
        }

        $rssConfig = $this->get('claroline.manager.rss_manager')->getConfig($widget);
        $form = $this->container->get('form.factory')->create(new ConfigType(), new Config());
        $form->bind($this->getRequest());

        if ($rssConfig) {
            if ($form->isValid()) {
                $rssConfig->setUrl($form->get('url')->getData());
            } else {
                return $this->render(
                  'ClarolineRssReaderBundle::formRss.html.twig',
                  array(
                      'form' => $form->createView(),
                      'isAdmin' => $widget->isAdmin(),
                      'config' => $widget,
                )
             );
            }
        } else {
            if ($form->isValid()) {
                $rssConfig = new Config();
                $rssConfig->setWidgetInstance($widget);
                $rssConfig->setUrl($form->get('url')->getData());
            } else {
                return $this->render(
                  'ClarolineRssReaderBundle::formRss.html.twig',
                  array(
                      'form' => $form->createView(),
                      'isAdmin' => $widget->isAdmin(),
                      'config' => $widget,
                  )
               );
            }
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($rssConfig);
        $em->flush();

        return new Response('', 204);
    }
}

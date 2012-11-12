<?php

namespace Claroline\RssReaderBundle\Listeners;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetEvent;
use Claroline\RssReaderBundle\Form\ConfigType;
use Claroline\RssReaderBundle\Entity\Config;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;

class RssReaderListener extends ContainerAware
{
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('Claroline\RssReaderBundle\Entity\Config');
        $widget = $this->container->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneBy(array('name' => 'claroline_rssreader'));

        //find the correct config
        if ($event->getWorkspace()!== null){

            $rssconfig = $repo->findOneBy(array('workspace' => $event->getWorkspace()->getId()));
            $dconfig = $this->container->get('claroline.widget.manager')->generateDisplayConfig($widget->getId(), $event->getWorkspace()->getId());

            if ($dconfig->getLvl() == DisplayConfig::ADMIN_LEVEL && $dconfig->isLocked() || $rssconfig == null){
                $rssconfig = $repo->findOneBy(array('isDefault' => true));
            }

        } else {
            $rssconfig = $repo->findOneBy(array('isDesktop' => true));
            //Taking the default workspace config. Temporary fix because the desktop configuration isn't done
            $rssconfig = $repo->findOneBy(array('isDefault' => true));
        }

        //check if the config is correct
        if ($rssconfig == null) {
            $event->setContent($this->container->get('translator')->trans('url_not_defined', array(), 'rss_reader'));
            $event->stopPropagation();
            return;
        }

        //read & use the config
        try{
            $rss = simplexml_load_file($rssconfig->getUrl());
        }
        catch(\Exception $e){
            $event->setContent($this->container->get('translator')->trans('rss_url_invalid', array(), 'rss_reader'));
            $event->stopPropagation();
            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineRssReaderBundle::rss.html.twig', array('rss' => $rss)
        );

        $event->setContent($content);
        $event->stopPropagation();
    }

    public function onConfigure(ConfigureWidgetEvent $event)
    {
        $workspace = $event->getWorkspace();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Claroline\RssReaderBundle\Entity\Config');

        //find the correct config (if it exists)

        if ($workspace != null){
            $config = $repo->findOneBy(array('workspace' => $workspace->getId()));
            $workspaceId = $workspace->getId();
        } else {
            $config = $repo->findOneBy(array('isDesktop' => $event->isDesktop(), 'isDefault' => $event->isDefault()));
            $workspaceId = 0;
        }

        if ($config == null) {
            $form = $this->container->get('form.factory')->create(new ConfigType, new Config());
            
            $content = $this->container->get('templating')->render(
                'ClarolineRssReaderBundle::form_workspace_create.html.twig', array(
                'form' => $form->createView(),
                'workspaceId' => $workspaceId,
                'isDefault' => $event->isDefault(),
                'isDesktop' => $event->isDesktop()
                )
            );
        } else {
            $form = $this->container->get('form.factory')->create(new ConfigType, $config);
            $content = $this->container->get('templating')->render(
                'ClarolineRssReaderBundle::form_workspace_update.html.twig', array(
                'form' => $form->createView(),
                'rssConfig' => $config
                )
            );
        }
        $event->setContent($content);
    }
}

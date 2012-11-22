<?php

namespace Claroline\RssReaderBundle\Listeners;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetWorkspaceEvent;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetDesktopEvent;
use Claroline\RssReaderBundle\Form\ConfigType;
use Claroline\RssReaderBundle\Entity\Config;

class RssReaderListener extends ContainerAware
{
    public function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('Claroline\RssReaderBundle\Entity\Config');
        $widget = $this->container->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneBy(array('name' => 'claroline_rssreader'));
        $rssconfig = $repo->findOneBy(array('workspace' => $event->getWorkspace()->getId()));

        if ($this->container->get('claroline.widget.manager')->isWorkspaceDefaultConfig($widget->getId(), $event->getWorkspace()->getId()) || $rssconfig == null){
            $rssconfig = $repo->findOneBy(array('isDefault' => true, 'isDesktop' => false));
        }

        //check if the config is correct
        if ($rssconfig == null) {
            $event->setContent($this->container->get('translator')->trans('url_not_defined', array(), 'rss_reader'));
            $event->stopPropagation();
            return;
        }

        $content = $this->getRssContent($rssconfig);
        $event->setContent($content);
        $event->stopPropagation();
    }

    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        $repo = $this->container->get('doctrine.orm.entity_manager')->getRepository('Claroline\RssReaderBundle\Entity\Config');
        $user = $this->container->get('security.context')->getToken()->getUser();
        $widget = $this->container->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneBy(array('name' => 'claroline_rssreader'));
        $rssconfig = $repo->findOneBy(array('user' => $user));

        if ($this->container->get('claroline.widget.manager')->isDesktopDefaultConfig($widget->getId(), $user->getId()) || $rssconfig == null){
            $rssconfig = $repo->findOneBy(array('isDefault' => true, 'isDesktop' => true));
        }

        //check if the config is correct
        if ($rssconfig == null) {
            $event->setContent($this->container->get('translator')->trans('url_not_defined', array(), 'rss_reader'));
            $event->stopPropagation();
            return;
        }

        $content = $this->getRssContent($rssconfig);
        $event->setContent($content);
        $event->stopPropagation();
    }

    public function onWorkspaceConfigure(ConfigureWidgetWorkspaceEvent $event)
    {
        $workspace = $event->getWorkspace();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Claroline\RssReaderBundle\Entity\Config');

        //find the correct config (if it exists)

        if ($workspace != null){
            $config = $repo->findOneBy(array('workspace' => $workspace->getId()));
            $workspaceId = $workspace->getId();
        } else {
            $config = $repo->findOneBy(array('isDesktop' => false, 'isDefault' => $event->isDefault()));
            $workspaceId = 0;
        }

        if ($config == null) {
            $form = $this->container->get('form.factory')->create(new ConfigType, new Config());

            $content = $this->container->get('templating')->render(
                'ClarolineRssReaderBundle::form_workspace_create.html.twig', array(
                'form' => $form->createView(),
                'workspaceId' => $workspaceId,
                'isDefault' => $event->isDefault(),
                'isDesktop' => false,
                'userId' => 0
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

    public function onDesktopConfigure(ConfigureWidgetDesktopEvent $event)
    {
        $user = $event->getUser();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Claroline\RssReaderBundle\Entity\Config');

        if ($user != null){
            $config = $repo->findOneBy(array('user' => $user->getId()));
            $userId = $user->getId();
        } else {
            $config = $repo->findOneBy(array('isDesktop' => true, 'isDefault' => $event->isDefault()));
            $userId = 0;
        }

        if ($config == null) {
            $form = $this->container->get('form.factory')->create(new ConfigType, new Config());

            $content = $this->container->get('templating')->render(
                'ClarolineRssReaderBundle::form_workspace_create.html.twig', array(
                'form' => $form->createView(),
                'workspaceId' => 0,
                'isDefault' => $event->isDefault(),
                'isDesktop' => true,
                'userId' => $userId
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

    private function getRssContent($rssconfig)
    {
        require(__DIR__.'/../Resources/vendor/syndexport.php');

        $rss = file_get_contents($rssconfig->getUrl());   
        $flux = new \SyndExport($rss);
        $items = $flux->exportItems();

        foreach ($items as $index => $item) {
            if(isset($items[$index]['description']))
            $items[$index]['description']=  preg_replace('/<[^>]+>/i', '', $item['description']);
        }
        
        return $this->container->get('templating')->render(
            'ClarolineRssReaderBundle::rss.html.twig', array('rss' => $items)
        );
    }
}

<?php

namespace Claroline\RssReaderBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Event\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\Event\ConfigureWidgetWorkspaceEvent;
use Claroline\CoreBundle\Event\Event\ConfigureWidgetDesktopEvent;
use Claroline\CoreBundle\Event\Event\ExportWidgetConfigEvent;
use Claroline\CoreBundle\Event\Event\ImportWidgetConfigEvent;
use Claroline\RssReaderBundle\Form\ConfigType;
use Claroline\RssReaderBundle\Entity\Config;

class RssReaderListener extends ContainerAware
{
    public function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $repo = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineRssReaderBundle:Config');
        $widget = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneBy(array('name' => 'claroline_rssreader'));
        $rssconfig = $repo->findOneBy(array('workspace' => $event->getWorkspace()->getId()));

        $isDefaultConfig = $this->container
            ->get('claroline.widget.manager')
            ->isWorkspaceDefaultConfig($widget->getId(), $event->getWorkspace()->getId());

        if ($isDefaultConfig || $rssconfig == null) {
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
        $repo = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineRssReaderBundle:Config');
        $user = $this->container->get('security.context')->getToken()->getUser();
        $widget = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneBy(array('name' => 'claroline_rssreader'));
        $rssconfig = $repo->findOneBy(array('user' => $user));
        $isDefaultConfig = $this->container
            ->get('claroline.widget.manager')
            ->isDesktopDefaultConfig($widget->getId(), $user->getId());

        if ($isDefaultConfig || $rssconfig == null) {
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
        $repo = $em->getRepository('ClarolineRssReaderBundle:Config');

        //find the correct config (if it exists)

        if ($workspace != null) {
            $config = $repo->findOneBy(array('workspace' => $workspace->getId()));
            $workspaceId = $workspace->getId();
        } else {
            $config = $repo->findOneBy(array('isDesktop' => false, 'isDefault' => $event->isDefault()));
            $workspaceId = 0;
        }

        if ($config == null) {
            $form = $this->container->get('form.factory')->create(new ConfigType, new Config());

            $content = $this->container->get('templating')->render(
                'ClarolineRssReaderBundle::formCreate.html.twig', array(
                'form' => $form->createView(),
                'workspaceId' => $workspaceId,
                'isDefault' => $event->isDefault(),
                'isDesktop' => false,
                'userId' => 0,
                )
            );
        } else {
            $form = $this->container->get('form.factory')->create(new ConfigType, $config);
            $content = $this->container->get('templating')->render(
                'ClarolineRssReaderBundle::formUpdate.html.twig', array(
                'form' => $form->createView(),
                'rssConfig' => $config,
                'layout' => 'none'
                )
            );
        }
        $event->setContent($content);
    }

    public function onDesktopConfigure(ConfigureWidgetDesktopEvent $event)
    {
        $user = $event->getUser();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ClarolineRssReaderBundle:Config');

        if ($user != null) {
            $config = $repo->findOneBy(array('user' => $user->getId()));
            $userId = $user->getId();
        } else {
            $config = $repo->findOneBy(array('isDesktop' => true, 'isDefault' => $event->isDefault()));
            $userId = 0;
        }

        if ($config == null) {
            $form = $this->container->get('form.factory')->create(new ConfigType, new Config());

            $content = $this->container->get('templating')->render(
                'ClarolineRssReaderBundle::formCreate.html.twig', array(
                'form' => $form->createView(),
                'workspaceId' => 0,
                'isDefault' => $event->isDefault(),
                'isDesktop' => true,
                'userId' => $userId,
                )
            );
        } else {
            $form = $this->container->get('form.factory')->create(new ConfigType, $config);
            $content = $this->container->get('templating')->render(
                'ClarolineRssReaderBundle::formUpdate.html.twig', array(
                'form' => $form->createView(),
                'rssConfig' => $config,
                )
            );
        }

        $event->setContent($content);
    }

    public function onExportConfig(ExportWidgetConfigEvent $event)
    {
        $repo = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineRssReaderBundle:Config');
        $rssconfig = $repo->findOneBy(array('workspace' => $event->getWorkspace()->getId()));

        $isDefaultConfig = $this->container
            ->get('claroline.widget.manager')
            ->isWorkspaceDefaultConfig($event->getWidget()->getId(), $event->getWorkspace()->getId());

        if ($isDefaultConfig || $rssconfig == null) {
            $rssconfig = $repo->findOneBy(array('isDefault' => true, 'isDesktop' => false));
        }

        if ($rssconfig !== null) {
            $config['url'] = $rssconfig->getUrl();
        } else {
            $config['url'] = null;
        }

        $event->setConfig($config);
        $event->stopPropagation();
    }

    public function onImportConfig(ImportWidgetConfigEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $data = $event->getConfig();
        if ($data['url'] != null) {
            $config = new Config();
            $config->setWorkspace($event->getWorkspace());
            $config->setUrl($data['url']);
            $config->setDesktop(false);
            $config->setDefault(false);
            $config->setUser(null);
            $em->persist($config);
            $em->flush();
            $event->stopPropagation();
        }
    }

    private function getRssContent($rssconfig)
    {
        // TODO : handle feed format exception...

        $items = $this->container->get('claroline.rss_reader.provider')
            ->getReaderFor(file_get_contents($rssconfig->getUrl()))
            ->getFeedItems();

        foreach ($items as $item) {
            $item->setDescription(preg_replace('/<[^>]+>/i', '', $item->getDescription()));
        }

        return $this->container->get('templating')->render(
            'ClarolineRssReaderBundle::rss.html.twig', array('rss' => $items)
        );
    }
}
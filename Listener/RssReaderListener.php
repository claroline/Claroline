<?php

namespace Claroline\RssReaderBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\RssReaderBundle\Form\ConfigType;
use Claroline\RssReaderBundle\Entity\Config;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\RssReaderBundle\Library\RssManager;
use Symfony\Component\Form\FormFactory;
use Claroline\RssReaderBundle\Library\ReaderProvider;
use JMS\DiExtraBundle\Annotation as DI;


/**
 * @DI\Service
 */
class RssReaderListener extends ContainerAware
{
    private $rssManager;
    private $formFactory;
    private $templating;
    private $sc;
    private $rssReader;

    /**
     * @DI\InjectParams({
     *      "rssManager" = @DI\Inject("claroline.manager.rss_manager"),
     *      "formFactory"       = @DI\Inject("form.factory"),
     *      "templating"        = @DI\Inject("templating"),
     *      "sc"                = @DI\Inject("security.context"),
     *      "rssReader"         = @DI\Inject("claroline.rss_reader.provider")
     * })
     */
    public function __construct(
        RssManager $rssManager,
        FormFactory $formFactory,
        TwigEngine $templating,
        SecurityContextInterface $sc,
        ReaderProvider $rssReader
    )
    {
        $this->rssManager = $rssManager;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->sc = $sc;
        $this->rssReader = $rssReader;
    }

    /**
     * @DI\Observe("widget_claroline_rssreader")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $config = $this->rssManager->getConfig($event->getInstance());
        if ($config) {
            $event->setContent($this->getRssContent($config));
        } else {
            $event->setContent('');
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_claroline_rssreader_configuration")
     */
    public function onConfigure(ConfigureWidgetEvent $event)
    {
        $instance = $event->getInstance();
        $config = $this->rssManager->getConfig($instance);

        if ($config === null) {
            $config = new Config();
        }

        $form = $this->formFactory->create(new ConfigType, $config);

           $content = $this->templating->render(
                'ClarolineRssReaderBundle::formRss.html.twig',
                array(
                    'form' => $form->createView(),
                    'isAdmin' => $instance->isAdmin(),
                    'config' => $instance
                )
           );
        $event->setContent($content);
    }

    private function getRssContent($rssconfig)
    {
        // TODO : handle feed format exception...
        $urlcontent = @file_get_contents($rssconfig->getUrl());

        if ($urlcontent === false) {
            return $this->templating->render('ClarolineRssReaderBundle::invalid.html.twig');
        }

        $items = $this->rssReader
            ->getReaderFor($urlcontent)
            ->getFeedItems(10);

        foreach ($items as $item) {
            $item->setDescription(preg_replace('/<[^>]+>/i', '', $item->getDescription()));
        }

        return $this->templating->render(
            'ClarolineRssReaderBundle::rss.html.twig', array('rss' => $items)
        );
    }
}

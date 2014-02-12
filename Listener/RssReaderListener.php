<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssReaderBundle\Listener;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\RssReaderBundle\Form\ConfigType;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Claroline\RssReaderBundle\Library\RssManager;
use Symfony\Component\Form\FormFactory;
use Claroline\RssReaderBundle\Library\ReaderProvider;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\RssReaderBundle\Entity\Config;


/**
 * @DI\Service
 */
class RssReaderListener
{
    private $rssManager;
    private $formFactory;
    private $templating;
    private $rssReader;

    /**
     * @DI\InjectParams({
     *      "rssManager" = @DI\Inject("claroline.manager.rss_manager"),
     *      "formFactory"       = @DI\Inject("form.factory"),
     *      "templating"        = @DI\Inject("templating"),
     *      "rssReader"         = @DI\Inject("claroline.rss_reader.provider")
     * })
     */
    public function __construct(
        RssManager $rssManager,
        FormFactory $formFactory,
        TwigEngine $templating,
        ReaderProvider $rssReader
    )
    {
        $this->rssManager = $rssManager;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
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
            $event->setContent($this->getRssContent($config, $event->getInstance()->getId()));
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

    private function getRssContent($rssConfig, $widgetId)
    {
        // TODO : handle feed format exception...
        $content = @file_get_contents($rssConfig->getUrl());

        if ($content === false) {
            return $this->templating->render('ClarolineRssReaderBundle::invalid.html.twig');
        }

        $items = $this->rssReader
            ->getReaderFor($content)
            ->getFeedItems(10);

        foreach ($items as $item) {
            $item->setDescription(preg_replace('/<[^>]+>/i', '', $item->getDescription()));
        }

        return $this->templating->render(
            'ClarolineRssReaderBundle::rss.html.twig',
            array('rss' => $items, 'widgetId' => $widgetId)
        );
    }
}

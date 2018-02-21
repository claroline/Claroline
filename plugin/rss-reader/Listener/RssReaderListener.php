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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\CopyWidgetConfigurationEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\RssReaderBundle\Entity\Config;
use Claroline\RssReaderBundle\Form\ConfigType;
use Claroline\RssReaderBundle\Library\ReaderProvider;
use Claroline\RssReaderBundle\Library\RssManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service
 */
class RssReaderListener
{
    private $rssManager;
    private $formFactory;
    private $templating;
    private $rssReader;
    private $om;

    /**
     * @DI\InjectParams({
     *      "rssManager"  = @DI\Inject("claroline.manager.rss_manager"),
     *      "formFactory" = @DI\Inject("form.factory"),
     *      "templating"  = @DI\Inject("templating"),
     *      "rssReader"   = @DI\Inject("claroline.rss_reader.provider"),
     *      "om"          = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        RssManager $rssManager,
        FormFactory $formFactory,
        TwigEngine $templating,
        ReaderProvider $rssReader,
        ObjectManager $om
    ) {
        $this->rssManager = $rssManager;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->rssReader = $rssReader;
        $this->om = $om;
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

        if (null === $config) {
            $config = new Config();
        }

        $form = $this->formFactory->create(new ConfigType(), $config);

        $content = $this->templating->render(
                'ClarolineRssReaderBundle::formRss.html.twig',
                [
                    'form' => $form->createView(),
                    'isAdmin' => $instance->isAdmin(),
                    'config' => $instance,
                ]
           );
        $event->setContent($content);
    }

    private function getRssContent($rssConfig, $widgetId)
    {
        // TODO : handle feed format exception...
        $data = file_get_contents($rssConfig->getUrl());
        $content = strstr($data, '<?xml');

        if (!$content && 0 === strpos($data, '<rss')) {
            $content = $data;
        }

        if (false === $content) {
            return $this->templating->render('ClarolineRssReaderBundle::invalid.html.twig');
        }

        try {
            $items = $this->rssReader
                ->getReaderFor($content)
                ->getFeedItems(10);
        } catch (\Exception $e) {
            return $this->templating->render('ClarolineRssReaderBundle::invalid.html.twig');
        }

        foreach ($items as $item) {
            $item->setDescription(preg_replace('/<[^>]+>/i', '', $item->getDescription()));
        }

        return $this->templating->render(
            'ClarolineRssReaderBundle::rss.html.twig',
            ['rss' => $items, 'widgetId' => $widgetId]
        );
    }

    /**
     * @DI\Observe("copy_widget_config_claroline_rssreader")
     *
     * @param CopyWidgetConfigurationEvent $event
     */
    public function onCopyWidgetConfiguration(CopyWidgetConfigurationEvent $event)
    {
        $source = $event->getWidgetInstance();
        $copy = $event->getWidgetInstanceCopy();

        $widgetConfig = $this->rssManager->getConfig($source);

        if (!is_null($widgetConfig)) {
            $widgetConfigCopy = new Config();
            $widgetConfigCopy->setWidgetInstance($copy);
            $widgetConfigCopy->setUrl($widgetConfig->getUrl());

            $this->om->persist($widgetConfigCopy);
            $this->om->flush();
        }
        $event->validateCopy();
        $event->stopPropagation();
    }
}

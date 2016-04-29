<?php

namespace Innova\AudioRecorderBundle\EventListener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Innova\AudioRecorderBundle\Manager\AudioRecorderManager;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Innova\AudioRecorderBundle\Form\Type\AudioRecorderConfigurationType;

/**
 *  @DI\Service()
 */
class AudioRecorderToolListener
{
    private $templating;
    private $container;
    private $arm;

  /**
   * @DI\InjectParams({
   *      "arm"               = @DI\Inject("innova.audio_recorder.manager"),
   *      "templating"        = @DI\Inject("templating"),
   *      "container"         = @DI\Inject("service_container")
   * })
   */
  public function __construct(AudioRecorderManager $arm, TwigEngine $templating, ContainerInterface $container)
  {
      $this->arm = $arm;
      $this->templating = $templating;
      $this->container = $container;
  }

  /**
   * @DI\Observe("open_tool_desktop_innova_audio_recorder_tool")
   *
   * @param DisplayToolEvent $event
   */
  public function onDesktopOpen(DisplayToolEvent $event)
  {
      $config = $this->arm->getConfig();
      $form = $this->container->get('form.factory')->create(new AudioRecorderConfigurationType(), $config);
      $content = $this->templating->render(
          'InnovaAudioRecorderBundle::desktopTool.html.twig',
          array('form' => $form->createView(), 'id' => $config->getId())
      );
      $event->setContent($content);
      $event->stopPropagation();
  }
}

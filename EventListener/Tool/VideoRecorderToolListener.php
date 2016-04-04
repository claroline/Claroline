<?php

namespace Innova\VideoRecorderBundle\EventListener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Innova\VideoRecorderBundle\Manager\VideoRecorderManager;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Innova\VideoRecorderBundle\Form\Type\VideoRecorderConfigurationType;

/**
 *  @DI\Service()
 */
class VideoRecorderToolListener
{
  private $templating;
  private $container;
  private $manager;

  /**
   * @DI\InjectParams({
   *      "manager"               = @DI\Inject("innova.video_recorder.manager"),
   *      "templating"        = @DI\Inject("templating"),
   *      "container"         = @DI\Inject("service_container")
   * })
   */
  public function __construct(VideoRecorderManager $manager, TwigEngine $templating, ContainerInterface $container)
  {
      $this->manager = $manager;
      $this->templating = $templating;
      $this->container = $container;
  }

  /**
   * @DI\Observe("open_tool_desktop_innova_video_recorder_tool")
   *
   * @param DisplayToolEvent $event
   */
  public function onDesktopOpen(DisplayToolEvent $event)
  {
      $config = $this->manager->getConfig();
      $form = $this->container->get('form.factory')->create(new VideoRecorderConfigurationType(), $config);
      $content = $this->templating->render(
          'InnovaVideoRecorderBundle::desktopTool.html.twig',
          array('form' => $form->createView(), 'id' => $config->getId())
      );
      $event->setContent($content);
      $event->stopPropagation();
  }
}

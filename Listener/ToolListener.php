<?php

namespace Innova\PathBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;

use Claroline\CoreBundle\Form\TextType;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;


class ToolListener
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    public function onWorkspaceOpen(DisplayToolEvent $event)
    {
        $id = $event->getWorkspace()->getId();
        $subRequest = $this->container->get('request')->duplicate(array('id'=>$id), array(), array("_controller" => 'InnovaPathBundle:Path:fromWorkspace'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
    
    public function onDesktopOpen(DisplayToolEvent $event)
    {
        $subRequest = $this->container->get('request')->duplicate(array(), array(), array("_controller" => 'InnovaPathBundle:Path:fromDesktop'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }

    /**
     * @DI\Observe("innova_player_text")
     *
     * @param OpenResourceEvent $event
     */
    public function onTextOpen(OpenResourceEvent $event)
    {
        $text = $event->getResource();
        $collection = new ResourceCollection(array($text->getResourceNode()));
        $isGranted = $this->container->get('security.context')->isGranted('WRITE', $collection);
        $revisionRepo = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\Revision');
        $content = $this->container->get('templating')->render(
            'InnovaPathBundle::Player/text.html.twig',
            array(
                'text' => $revisionRepo->getLastRevision($text)->getContent(),
                '_resource' => $text,
                'isEditGranted' => $isGranted
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}

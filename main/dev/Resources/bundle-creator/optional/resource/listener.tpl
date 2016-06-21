<?php

namespace [[Vendor]]\[[Bundle]]Bundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Event\ImportResourceTemplateEvent;
use [[Vendor]]\[[Bundle]]Bundle\Entity\[[Resource_Type]];
use [[Vendor]]\[[Bundle]]Bundle\Form\[[Resource_Type]]Type;

/**
 *  @DI\Service()
 */
class [[Resource_Type]]ResourceListener
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @DI\Observe("create_form_[[vendor]]_[[resource_type]]")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new [[Resource_Type]]Type(), new [[Resource_Type]]());
        $content = $this->container->get('templating')->render(
            '[[Vendor]][[Bundle]]Bundle:[[Resource_Type]]:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => '[[vendor]]_[[resource_type]]'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_[[vendor]]_[[resource_type]]")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new [[Resource_Type]]Type(), new [[Resource_Type]]());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $event->setPublished($published);
            $event->setResources(array($form->getData()));
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            '[[Vendor]][[Bundle]]Bundle:[[Resource_Type]]:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => $event->getResourceType()
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_[[vendor]]_[[resource_type]]")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_[[vendor]]_[[resource_type]]")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $newRes = null;
        $event->setCopy($newRes);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("download_[[vendor]]_[[resource_type]]")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        $path = '/path/to/dledfile';
        $event->setItem($path);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_[[vendor]]_[[resource_type]]")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $response = null;
        $event->setResponse($response);
        $event->stopPropagation();
    }
}

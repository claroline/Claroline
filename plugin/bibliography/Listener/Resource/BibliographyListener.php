<?php

namespace Icap\BibliographyBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Icap\BibliographyBundle\Entity\BookReference;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service()
 */
class BibliographyListener
{
    /** @var EngineInterface */
    private $templating;

    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * BibliographyListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"           = @DI\Inject("templating"),
     *     "objectManager"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"           = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param EngineInterface    $templating
     * @param ObjectManager      $objectManager
     * @param SerializerProvider $serializer
     */
    public function __construct(
        EngineInterface $templating,
        ObjectManager $objectManager,
        SerializerProvider $serializer
    ) {
        $this->templating = $templating;
        $this->serializer = $serializer;
        $this->om = $objectManager;
    }

    /**
     * Loads a Bibliography resource.
     *
     * @DI\Observe("resource.icap_bibliography.load")
     *
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        $event->setData([
            'bookReference' => $this->serializer->serialize($event->getResource()),
        ]);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_icap_bibliography")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $bookReference = $event->getResource();
        $content = $this->templating->render(
            'IcapBibliographyBundle:book_reference:open.html.twig',
            [
                '_resource' => $bookReference,
            ]
        );
        $response = new Response($content);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_icap_bibliography")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var BookReference $old */
        $old = $event->getResource();
        $new = new BookReference();

        $new->setAuthor($old->getAuthor());
        $new->setDescription($old->getDescription());
        $new->setAbstract($old->getAbstract());
        $new->setIsbn($old->getIsbn());
        $new->setPublisher($old->getPublisher());
        $new->setPrinter($old->getPrinter());
        $new->setPublicationYear($old->getPublicationYear());
        $new->setLanguage($old->getLanguage());
        $new->setPageCount($old->getPageCount());
        $new->setUrl($old->getUrl());
        $new->setCoverUrl($old->getCoverUrl());

        $this->om->persist($new);
        $this->om->flush();

        $event->setCopy($new);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_icap_bibliography")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}

<?php

namespace Icap\BibliographyBundle\Listener;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Icap\BibliographyBundle\Repository\BookReferenceRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service
 */
class WidgetListener
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $templatingEngine;

    private $repository;

    /**
     * @DI\InjectParams({
     *      "formFactory"      = @DI\Inject("form.factory"),
     *      "templatingEngine" = @DI\Inject("templating"),
     *      "repository"       = @DI\Inject("icap_bibliography.repository.book_reference")
     * })
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EngineInterface $templatingEngine,
        BookReferenceRepository $repository
    ) {
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->repository = $repository;
    }

    /**
     * @DI\Observe("widget_book_reference_list")
     */
    public function onWidgetBookReferenceListDisplay(DisplayWidgetEvent $event)
    {
        $workspace = $event->getInstance()->getWorkspace();
        $bookReferences = $this->repository->findAllByWorkspace($workspace);

        $content = $this->templatingEngine->render(
            'IcapBibliographyBundle:widget:bibliography.html.twig',
            [
                'bookReferences' => $bookReferences,
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}

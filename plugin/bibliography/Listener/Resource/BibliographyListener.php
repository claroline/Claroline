<?php

namespace Icap\BibliographyBundle\Listener\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\LinkBundle\Entity\Resource\Shortcut;
use Icap\BibliographyBundle\Entity\BookReference;
use Icap\BibliographyBundle\Form\BookReferenceType;
use Icap\BibliographyBundle\Manager\BookReferenceManager;
use Icap\BibliographyBundle\Repository\BookReferenceConfigurationRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service()
 */
class BibliographyListener
{
    use PermissionCheckerTrait;

    /** @var null|\Symfony\Component\HttpFoundation\Request */
    private $request;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $templating;

    /** @var null|\Claroline\CoreBundle\Entity\User */
    private $user;

    /** @var ObjectManager */
    private $om;

    /** @var BookReferenceManager */
    protected $bookReferenceManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"                = @DI\Inject("form.factory"),
     *     "templating"                 = @DI\Inject("templating"),
     *     "tokenStorage"               = @DI\Inject("security.token_storage"),
     *     "objectManager"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"               = @DI\Inject("request_stack"),
     *     "bookReferenceManager"       = @DI\Inject("icap.bookReference.manager")
     * })
     *
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface $templating
     * @param TokenStorageInterface $tokenStorage
     * @param ObjectManager $objectManager
     * @param RequestStack $requestStack
     * @param BookReferenceManager $bookReferenceManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EngineInterface $templating,
        TokenStorageInterface $tokenStorage,
        ObjectManager $objectManager,
        RequestStack $requestStack,
        BookReferenceManager $bookReferenceManager
    ) {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->om = $objectManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->bookReferenceManager = $bookReferenceManager;
    }

    /**
     * @DI\Observe("create_icap_bibliography")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new BookReferenceType(), new BookReference());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            /** @var BookReference $bookResource */
            $bookResource = $form->getData();

            $bookReferenceInWorkspace = $this->bookReferenceManager->bookExistsInWorkspace($bookResource->getIsbn(), $event->getParent()->getWorkspace());

            if (null !== $bookReferenceInWorkspace) {
                // Book already exists, create a link instead of a new resource

                $resourceShortcut = new Shortcut();
                $resourceShortcut->setTarget($bookReferenceInWorkspace->getResourceNode());
                $resourceShortcut->setName($bookReferenceInWorkspace->getResourceNode()->getName());

                $event->setResourceType('shortcut');
                $event->setParent($bookReferenceInWorkspace->getResourceNode());
                $event->setResources([$resourceShortcut]);
            } else {
                // Create book as a new resource
                $event->setResources([$bookResource]);
            }
        } else {
            $content = $this->templating->render(
                'ClarolineCoreBundle:resource:create_form.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'icap_bibliography',
                ]
            );
            $event->setErrorFormContent($content);
        }
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
        $this->checkPermission('OPEN', $bookReference->getResourceNode(), [], true);
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
        $this->om->remove($event->getResource());
        $this->om->flush();
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_bibliographybundle")
     *
     * @param PluginOptionsEvent $event
     */
    public function onConfig(PluginOptionsEvent $event)
    {
        // If user is not platform admin, deny access
        if (!$this->user->hasRole('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException("Only platform administrators can configure plugins");
        }

        $content = $this->templating->render(
            'IcapBibliographyBundle:configuration:open.html.twig',
            [
                'configuration' => $this->bookReferenceManager->getConfig()
            ]
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}

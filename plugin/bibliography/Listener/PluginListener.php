<?php

namespace Icap\BibliographyBundle\Listener;

use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Icap\BibliographyBundle\Manager\BookReferenceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service
 */
class PluginListener
{
    /** @var EngineInterface */
    private $templating;

    /** @var null|\Claroline\CoreBundle\Entity\User */
    private $user;

    /** @var BookReferenceManager */
    private $bookReferenceManager;

    /**
     * PluginListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"            = @DI\Inject("templating"),
     *     "tokenStorage"         = @DI\Inject("security.token_storage"),
     *     "bookReferenceManager" = @DI\Inject("icap.bookReference.manager")
     * })
     *
     * @param EngineInterface       $templating
     * @param TokenStorageInterface $tokenStorage
     * @param BookReferenceManager  $bookReferenceManager
     */
    public function __construct(
        EngineInterface $templating,
        TokenStorageInterface $tokenStorage,
        BookReferenceManager $bookReferenceManager
    ) {
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->bookReferenceManager = $bookReferenceManager;
    }

    /**
     * @DI\Observe("plugin_options_bibliographybundle")
     *
     * @param PluginOptionsEvent $event
     */
    public function onConfig(PluginOptionsEvent $event)
    {
        // If user is not platform admin, deny access
        if (
          !$this->tokenStorage->getToken() &&
          'anon.' !== $this->tokenStorage->getToken()->getUser() &&
          !$this->tokenStorage->getToken()->getUser()->hasRole('ROLE_ADMIN')
        ) {
            throw new AccessDeniedHttpException(
                'Only platform administrators can configure plugins'
            );
        }

        $content = $this->templating->render(
            'IcapBibliographyBundle:configuration:open.html.twig',
            [
                'configuration' => $this->bookReferenceManager->getConfig(),
            ]
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}

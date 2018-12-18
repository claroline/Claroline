<?php

namespace HeVinci\FavouriteBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service
 */
class FavouriteListener
{
    use ContainerAwareTrait;

    private $om;
    private $tokenStorage;
    private $router;
    private $templatingEngine;

    /**
     * @DI\InjectParams({
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "router"           = @DI\Inject("router"),
     *      "templatingEngine" = @DI\Inject("templating")
     * })
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        Router $router,
        EngineInterface $templatingEngine
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->templatingEngine = $templatingEngine;
    }

    /**
     * @DI\Observe("resource_action_hevinci_favourite")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onFavoriteAction(CustomActionResourceEvent $event)
    {
        $nodeId = $event->getResource()->getResourceNode()->getId();
        $favourite = $this->om->getRepository('HeVinciFavouriteBundle:Favourite')
            ->findBy([
                'resourceNode' => $nodeId,
                'user' => $this->tokenStorage->getToken()->getUser(),
            ]);

        $content = $this->templatingEngine->render(
            'HeVinciFavouriteBundle:Favourite:form.html.twig',
            [
                'isFavourite' => (bool) $favourite,
                'nodeId' => $nodeId,
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }
}

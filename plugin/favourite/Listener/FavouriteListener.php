<?php

namespace HeVinci\FavouriteBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service
 */
class FavouriteListener extends ContainerAware
{
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

    /**
     * @DI\Observe("widget_hevinci_favourite_widget")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $widgetInstance = $event->getInstance();
        $workspace = $widgetInstance->getWorkspace();
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = $user === 'anon.';
        $favourites = [];

        if (!$isAnon) {
            $favouriteRepo = $this->om->getRepository('HeVinciFavouriteBundle:Favourite');
            $favourites = is_null($workspace) ?
                $favouriteRepo->findBy(['user' => $user]) :
                $favouriteRepo->findFavouritesByUserAndWorkspace($user, $workspace);
        }
        $content = $this->templatingEngine->render(
            'HeVinciFavouriteBundle:widget:favourite.html.twig',
            ['favourites' => $favourites]
        );
        $event->setContent($content);
        $event->stopPropagation();
    }
}

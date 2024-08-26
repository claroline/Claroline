<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Event\GlobalSearchEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/search")
 */
class SearchController
{
    public function __construct(
        private readonly PlatformConfigurationHandler $config,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    /**
     * Search elements in the platform.
     * It searches in :
     *   - Workspace : name, code.
     *   - Resource : name.
     *   - User : username, firstName, lastName, email.
     *
     * @Route("/{search}", name="claro_search")
     */
    public function searchAction(string $search): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $searchConfig = $this->config->getParameter('search');
        $searchableItems = array_filter(array_keys($searchConfig['items']), function ($itemName) use ($searchConfig) {
            return isset($searchConfig['items'][$itemName]) && $searchConfig['items'][$itemName];
        });

        $searchEvent = new GlobalSearchEvent($search, $searchConfig['limit'], $searchableItems);

        $this->dispatcher->dispatch($searchEvent);

        return new JsonResponse($searchEvent->getResults());
    }
}

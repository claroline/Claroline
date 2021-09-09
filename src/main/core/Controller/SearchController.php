<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/search", options={"expose"=true})
 */
class SearchController
{
    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        PlatformConfigurationHandler $config,
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
        $this->config = $config;
        $this->om = $om;
        $this->serializer = $serializer;
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
        $results = [];

        $searchConfig = $this->config->getParameter('header.search');
        if (isset($searchConfig['user']) && $searchConfig['user']) {
            $users = $this->om->getRepository(User::class)->search($search, 5);

            $results['users'] = array_map(function (User $user) {
                return $this->serializer->serialize($user, [Options::SERIALIZE_MINIMAL]);
            }, $users);
        }

        if (isset($searchConfig['workspace']) && $searchConfig['workspace']) {
            $workspaces = $this->om->getRepository(Workspace::class)->search($search, 5);

            $results['workspaces'] = array_map(function (Workspace $workspace) {
                return $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }, $workspaces);
        }

        if (isset($searchConfig['resource']) && $searchConfig['resource']) {
            $resources = $this->om->getRepository(ResourceNode::class)->search($search, 5);

            $results['resources'] = array_map(function (ResourceNode $resource) {
                return $this->serializer->serialize($resource, [Options::SERIALIZE_MINIMAL]);
            }, $resources);
        }

        return new JsonResponse($results);
    }
}

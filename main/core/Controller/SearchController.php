<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @EXT\Route("/search", options={"expose"=true})
 */
class SearchController
{
    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * SearchController constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
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
     * @EXT\Route("/{search}", name="claro_search")
     *
     * @param string $search
     *
     * @return JsonResponse
     */
    public function searchAction($search)
    {
        $workspaces = $this->om->getRepository(Workspace::class)->search($search, 5);
        $resources = $this->om->getRepository(ResourceNode::class)->search($search, 5);
        $users = $this->om->getRepository(User::class)->search($search, 5);

        return new JsonResponse([
            'workspaces' => array_map(function (Workspace $workspace) {
                return $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }, $workspaces),
            'resources' => array_map(function (ResourceNode $resource) {
                return $this->serializer->serialize($resource, [Options::SERIALIZE_MINIMAL]);
            }, $resources),
            'users' => array_map(function (User $user) {
                return $this->serializer->serialize($user, [Options::SERIALIZE_MINIMAL]);
            }, $users),
        ]);
    }
}

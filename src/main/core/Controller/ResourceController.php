<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\ResourceRestrictionsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Manages platform resources.
 * ATTENTION. be careful if you change routes order.
 *
 * @Route("/resources")
 */
class ResourceController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly ResourceManager $manager,
        private readonly ResourceActionManager $actionManager,
        private readonly ResourceRestrictionsManager $restrictionsManager,
        private readonly ObjectManager $om,
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    /**
     * Opens a resource.
     *
     * @Route("/load/{id}", name="claro_resource_load", methods={"GET"})
     * @Route("/load/{id}/embedded/{embedded}", name="claro_resource_load_embedded", methods={"GET"})
     */
    public function openAction(string $id, int $embedded = 0): JsonResponse
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->om->getRepository(ResourceNode::class)->findOneByUuidOrSlug($id);
        if (!$resourceNode) {
            return new JsonResponse('Resource not found.', 404);
        }

        // gets the current user roles to check access restrictions
        $userRoles = $this->tokenStorage->getToken()->getRoleNames();
        $accessErrors = $this->restrictionsManager->getErrors($resourceNode, $userRoles);
        $isManager = $this->manager->isManager($resourceNode);

        if (empty($accessErrors) || $isManager) {
            $loaded = $this->manager->load($resourceNode, $embedded ? true : false);

            return new JsonResponse(
                array_merge($loaded, [
                    'managed' => $isManager,
                    'resourceNode' => $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]),
                    // append access restrictions to the loaded node if any
                    // to let the manager knows that other users can not enter the resource
                    'accessErrors' => $accessErrors,
                ])
            );
        }

        return new JsonResponse([
            'managed' => false,
            'resourceNode' => $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]),
            'accessErrors' => $accessErrors,
        ], 403);
    }

    /**
     * Embeds a resource inside a rich text content.
     *
     * @Route("/embed/{id}", name="claro_resource_embed")
     */
    public function embedAction(ResourceNode $resourceNode): Response
    {
        return new Response($this->manager->embed($resourceNode));
    }

    /**
     * Downloads a list of Resources.
     *
     * @Route("/download", name="claro_resource_download")
     *
     * @return JsonResponse|BinaryFileResponse
     */
    public function downloadAction(Request $request): Response
    {
        $nodes = $this->decodeIdsString($request, ResourceNode::class);

        $collection = new ResourceCollection($nodes);
        if (!$this->authorization->isGranted('EXPORT', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $data = $this->manager->download($nodes);

        $file = $data['file'];
        $fileName = $data['name'];

        if (!file_exists($file)) {
            return new JsonResponse('File not found.', 500);
        }

        if ($fileName) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $fileName = TextNormalizer::toKey(str_replace('.'.$ext, '', $fileName)).'.'.$ext;
        }

        return new BinaryFileResponse($file, 200, [
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);
    }

    /**
     * Submit access code.
     *
     * @Route("/unlock/{id}", name="claro_resource_unlock", methods={"POST"})
     *
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
     */
    public function unlockAction(ResourceNode $resourceNode, Request $request): JsonResponse
    {
        $this->restrictionsManager->unlock($resourceNode, json_decode($request->getContent(), true)['code']);

        return new JsonResponse(null, 204);
    }

    /**
     * Executes an action on a collection of resources.
     *
     * @Route("/collection/{action}", name="claro_resource_collection_action")
     */
    public function executeCollectionAction(string $action, Request $request): JsonResponse
    {
        /** @var ResourceNode[] $resourceNodes */
        $resourceNodes = $this->decodeIdsString($request, ResourceNode::class);

        $responses = [];

        // read request and get user query
        $parameters = $request->query->all();
        $content = $this->decodeRequest($request);
        $files = $request->files->all();

        $this->om->startFlushSuite();

        foreach ($resourceNodes as $resourceNode) {
            // check the requested action exists
            if (!$this->actionManager->support($resourceNode, $action, $request->getMethod())) {
                // undefined action
                throw new NotFoundHttpException(sprintf('The action %s with method [%s] does not exist for resource type %s.', $action, $request->getMethod(), $resourceNode->getResourceType()->getName()));
            }

            // check current user rights
            $this->checkAccess($this->actionManager->get($resourceNode, $action), [$resourceNode], $parameters);

            // dispatch action event
            $responses[] = $this->actionManager->execute($resourceNode, $action, $parameters, $content, $files);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Response $response) {
            return json_decode($response->getContent(), true);
        }, $responses));
    }

    /**
     * Executes an action on one resource.
     *
     * @Route("/{action}/{id}", name="claro_resource_action")
     *
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
     */
    public function executeAction(string $action, ResourceNode $resourceNode, Request $request): Response
    {
        // check the requested action exists
        if (!$this->actionManager->support($resourceNode, $action, $request->getMethod())) {
            // undefined action
            throw new NotFoundHttpException(sprintf('The action %s with method [%s] does not exist for resource type %s.', $action, $request->getMethod(), $resourceNode->getResourceType()->getName()));
        }

        // read request and get user query
        $parameters = $request->query->all();
        $content = $this->decodeRequest($request);
        $files = $request->files->all();

        // check current user rights
        $this->checkAccess($this->actionManager->get($resourceNode, $action), [$resourceNode], $parameters);

        // dispatch action event
        return $this->actionManager->execute($resourceNode, $action, $parameters, $content, $files);
    }

    /**
     * Checks the current user can execute the action on the requested nodes.
     */
    private function checkAccess(MenuAction $action, array $resourceNodes, array $attributes = []): void
    {
        $collection = new ResourceCollection($resourceNodes);
        $collection->setAttributes($attributes);

        if (!$this->actionManager->hasPermission($action, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}

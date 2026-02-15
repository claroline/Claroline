<?php

namespace Claroline\LogBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\LogBundle\Entity\OperationalLog;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/log/operational')]
class OperationalLogController
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly ContextProvider $contextProvider,
        private readonly Crud $crud
    ) {
    }

    #[Route(path: '/context/{context}/{contextId}', name: 'apiv2_logs_operational', methods: ['GET'])]
    public function listByContextAction(
        string $context,
        ?string $contextId = null,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        try {
            $contextHandler = $this->contextProvider->getContext($context, $contextId);
            $contextSubject = $contextHandler->getObject($contextId);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->authorization->isGranted('EDIT', $contextSubject)) {
            throw new AccessDeniedException();
        }

        $finderRequest
            ->addFilter('contextId', $contextSubject?->getUuid())
            ->addFilter('contextName', $context);

        $logs = $this->crud->search(OperationalLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }

    /**
     * Lists all the operational logs for an object.
     *
     * @param string $objectName - the FQCN of the target entity without \ (ex. ClarolineCoreBundleEntityUser)
     */
    #[Route(path: '/object/{objectId}/{objectName}', name: 'apiv2_logs_operational_object', requirements: ['objectName' => '.+'], methods: ['GET'])]
    public function listByObjectAction(
        string $objectId,
        ?string $objectName = null,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        try {
            // I can't find a way to get \ in the URL directly (route never matches)
            $objectName = str_replace('/', '\\', $objectName);

            $object = $this->om->getRepository($objectName)->findOneBy(['uuid' => $objectId]);
            if (empty($object)) {
                throw new NotFoundHttpException('Object not found.');
            }
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->authorization->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $finderRequest
            ->addFilter('objectId', $objectId);

        $logs = $this->crud->search(OperationalLog::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $logs->toResponse();
    }
}

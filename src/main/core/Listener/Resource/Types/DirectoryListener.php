<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource\Types;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Integrates the "Directory" resource.
 */
class DirectoryListener
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var ResourceActionManager */
    private $actionManager;
    /** @var RightsManager */
    private $rightsManager;
    /** @var ParametersSerializer */
    private $parametersSerializer;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ResourceManager $resourceManager,
        ResourceActionManager $actionManager,
        RightsManager $rightsManager,
        ParametersSerializer $parametersSerializer
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->actionManager = $actionManager;
        $this->parametersSerializer = $parametersSerializer;
    }

    /**
     * Loads a directory.
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $parameters = $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]);
        $storageLock = isset($parameters['restrictions']['storage']) &&
            isset($parameters['restrictions']['max_storage_reached']) &&
            $parameters['restrictions']['storage'] &&
            $parameters['restrictions']['max_storage_reached'];

        $event->setData([
            'directory' => $this->serializer->serialize($event->getResource()),
            'storageLock' => $storageLock,
        ]);

        $event->stopPropagation();
    }

    /**
     * Adds a new resource inside a directory.
     */
    public function onAdd(ResourceActionEvent $event)
    {
        $data = $event->getData();
        $parent = $event->getResourceNode();

        $add = $this->actionManager->get($parent, 'add');

        // checks if the current user can add
        $collection = new ResourceCollection([$parent], ['type' => $data['resourceNode']['meta']['type']]);
        if (!$this->actionManager->hasPermission($add, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $options = $event->getOptions();

        // create the resource node

        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->crud->create(ResourceNode::class, $data['resourceNode'], $options);
        $resourceNode->setParent($parent);
        $resourceNode->setWorkspace($parent->getWorkspace());

        // initialize custom resource Entity
        $resourceClass = $resourceNode->getResourceType()->getClass();

        /** @var AbstractResource $resource */
        $resource = $this->crud->create($resourceClass, !empty($data['resource']) ? $data['resource'] : [], $options);
        $resource->setResourceNode($resourceNode);

        // maybe do it in the serializer (if it can be done without intermediate flush)
        if (!empty($data['resourceNode']['rights'])) {
            foreach ($data['resourceNode']['rights'] as $rights) {
                /** @var Role $role */
                $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $rights['name']]);

                $creation = [];
                if (!empty($rights['permissions']['create']) && $resource instanceof Directory) {
                    // only forward creation rights to resource which can handle it (only directories atm)
                    $creation = $rights['permissions']['create'];
                }
                $this->rightsManager->update($rights['permissions'], $role, $resourceNode, false, $creation);
            }
        } else {
            // todo : initialize default rights
        }

        $this->om->persist($resource);
        $this->om->persist($resourceNode);

        $this->om->flush();

        // todo : dispatch get/load action instead
        $event->setResponse(new JsonResponse(
            [
                'resourceNode' => $this->serializer->serialize($resourceNode),
                'resource' => $this->serializer->serialize($resource),
            ],
            201
        ));
    }
}

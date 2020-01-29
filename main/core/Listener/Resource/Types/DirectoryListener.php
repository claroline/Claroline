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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Listener\Log\LogListener;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Integrates the "Directory" resource.
 */
class DirectoryListener
{
    /** @var SerializerProvider */
    private $serializer;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var RightsManager */
    private $rightsManager;

    /** @var ParametersSerializer */
    private $parametersSerializer;

    /**
     * DirectoryListener constructor.
     *
     * @param ObjectManager         $om
     * @param SerializerProvider    $serializer
     * @param Crud                  $crud
     * @param ResourceManager       $resourceManager
     * @param ResourceActionManager $actionManager
     * @param RightsManager         $rightsManager
     * @param LogListener           $logListener
     * @param ParametersSerializer  $parametersSerializer
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ResourceManager $resourceManager,
        ResourceActionManager $actionManager,
        RightsManager $rightsManager,
        LogListener $logListener,
        ParametersSerializer $parametersSerializer
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->actionManager = $actionManager;
        $this->logListener = $logListener;
        $this->parametersSerializer = $parametersSerializer;
    }

    /**
     * Loads a directory.
     *
     * @param LoadResourceEvent $event
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
     *
     * @param ResourceActionEvent $event
     */
    public function onAdd(ResourceActionEvent $event)
    {
        $data = $event->getData();
        $parent = $event->getResourceNode();
        $this->logListener->disable();
        $add = $this->actionManager->get($parent, 'add');

        // checks if the current user can add
        $collection = new ResourceCollection([$parent], ['type' => $data['resourceNode']['meta']['type']]);
        if (!$this->actionManager->hasPermission($add, $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }

        $options = $event->getOptions();
        $options[] = Options::IGNORE_CRUD_POST_EVENT;

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
                $role = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneBy(['name' => $rights['name']]);
                $this->rightsManager->editPerms($rights['permissions'], $role, $resourceNode);
            }
        } else {
            // todo : initialize default rights
        }
        $this->logListener->enable();
        $this->crud->dispatch('create', 'post', [$resource, $options]);
        $this->om->persist($resource);
        $this->om->persist($resourceNode);

        // todo : dispatch creation event

        $this->om->flush();

        $this->crud->dispatch('create', 'post', [$resourceNode]);

        // todo : dispatch get/load action instead
        $event->setResponse(new JsonResponse(
            [
                'resourceNode' => $this->serializer->serialize($resourceNode),
                'resource' => $this->serializer->serialize($resource),
            ],
            201
        ));
    }

    /**
     * Creates a new directory.
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * Removes a directory.
     *
     * @param deleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}

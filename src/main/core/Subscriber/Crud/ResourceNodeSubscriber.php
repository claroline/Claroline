<?php

namespace Claroline\CoreBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResourceNodeSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var FileManager */
    private $fileManager;
    /** @var ResourceLifecycleManager */
    private $lifeCycleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var RightsManager */
    private $rightsManager;
    /** @var ResourceNodeSerializer */
    private $serializer;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        Crud $crud,
        FileManager $fileManager,
        ResourceLifecycleManager $lifeCycleManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        ResourceNodeSerializer $serializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->fileManager = $fileManager;
        $this->lifeCycleManager = $lifeCycleManager;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', ResourceNode::class) => 'preCreate',
            Crud::getEventName('copy', 'pre', ResourceNode::class) => 'preCopy',
            Crud::getEventName('copy', 'post', ResourceNode::class) => 'postCopy',
            Crud::getEventName('delete', 'pre', ResourceNode::class) => 'preDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();

        // set the creator of the resource
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $resourceNode->setCreator($user);
        }
    }

    public function preDelete(DeleteEvent $event)
    {
        /** @var ResourceNode $node */
        $node = $event->getObject();
        $options = $event->getOptions();

        // check if the node is still correctly linked to an AbstractResource
        // this was a common problem in the old versions
        $resource = $this->resourceManager->getResourceFromNode($node);
        if (empty($resource)) {
            // partially broken data found, we cannot go further but don't want to break everything
            return;
        }

        // forward delete event to the resources implementations
        // TODO : listen to crud event instead
        $event = $this->lifeCycleManager->delete($node, in_array(Options::SOFT_DELETE, $options));

        $this->crud->delete($resource, array_merge([], $options, $event->isSoftDelete() ? [Options::SOFT_DELETE] : []));

        // we check softDelete flag from custom event because some resource can force it
        // if they don't want to be removed (eg. quizzes with papers attached on it)
        if ($event->isSoftDelete()) {
            $node->setActive(false);
            $this->om->persist($node);
        } else {
            // remove resource files
            if ($node->getPoster()) {
                $this->fileManager->unlinkFile(ResourceNode::class, $node->getUuid(), $node->getPoster());
            }

            if ($node->getThumbnail()) {
                $this->fileManager->unlinkFile(ResourceNode::class, $node->getUuid(), $node->getThumbnail());
            }

            foreach ($event->getFiles() as $file) {
                $this->fileManager->remove($file, true);
            }
        }
    }

    public function preCopy(CopyEvent $event)
    {
        /** @var ResourceNode $node */
        $node = $event->getObject();
        /** @var ResourceNode $newNode */
        $newNode = $event->getCopy();

        $resource = $this->resourceManager->getResourceFromNode($node);
        if (!$resource) {
            // if something is malformed in production, try to not break everything if we don't need to. Just return null.
            return;
        }

        /** @var ResourceNode $newParent */
        $newParent = $event->getExtra()['parent'];

        // set the creator of the copy
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $newNode->setCreator($user);
        }

        // link new node to its parent
        $newNode->setWorkspace($newParent->getWorkspace());
        $newNode->setParent($newParent);
        $newParent->addChild($newNode);

        /** @var AbstractResource $copy */
        $copy = $this->crud->copy($resource);

        // link node and abstract resource
        $copy->setResourceNode($newNode);
        // unmapped but allow to retrieve it with the entity without any request for the following code
        $newNode->setResource($copy);

        $this->om->persist($newNode);
        $this->om->persist($copy);

        // TODO : this should not use a serializer internal method and should be done in post event to avoid more flush
        $this->serializer->deserializeRights(array_values($this->rightsManager->getRights($newParent)), $newNode);

        // TODO : listen to crud copy event instead
        $this->lifeCycleManager->copy($resource, $copy);
    }

    public function postCopy(CopyEvent $event)
    {
        /** @var ResourceNode $node */
        $node = $event->getObject();
        /** @var ResourceNode $newNode */
        $newNode = $event->getCopy();

        // TODO : move this in the Directory listener
        if ('directory' === $node->getResourceType()->getName()) {
            // this is needed because otherwise I don't get the new node rights.
            // rights are directly created/updated in DB so the ResourceNode::getRights returns outdated data for now
            $this->om->refresh($newNode);

            foreach ($node->getChildren() as $child) {
                if ($child->isActive()) {
                    $this->crud->copy($child, [Options::NO_RIGHTS, Crud::NO_PERMISSIONS], ['parent' => $newNode]);
                }
            }
        }
    }
}

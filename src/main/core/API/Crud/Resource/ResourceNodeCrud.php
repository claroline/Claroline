<?php

namespace Claroline\CoreBundle\API\Crud\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResourceNodeCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ResourceLifecycleManager */
    private $lifeCycleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var RightsManager */
    private $rightsManager;
    /** @var ResourceNodeSerializer */
    private $serializer;
    /** @var string */
    private $filesDirectory;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        Crud $crud,
        StrictDispatcher $dispatcher,
        ResourceLifecycleManager $lifeCycleManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        ResourceNodeSerializer $serializer,
        string $filesDirectory
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->dispatcher = $dispatcher;
        $this->lifeCycleManager = $lifeCycleManager;
        $this->filesDirectory = $filesDirectory;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->serializer = $serializer;
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
        $node = $event->getObject();
        $options = $event->getOptions();
        $softDelete = in_array(Options::SOFT_DELETE, $options);

        if (null === $node->getParent()) {
            throw new \LogicException('Root directory cannot be removed');
        }

        $workspace = $node->getWorkspace();
        $nodes = $this->om->getRepository(ResourceNode::class)->findDescendants($node);
        $nodes[] = $node;
        $this->om->startFlushSuite();

        foreach ($nodes as $node) {
            $resource = $this->resourceManager->getResourceFromNode($node);
            /*
             * resChild can be null if a shortcut was removed
             *
             * activities must be ignored atm
             */

            $ignore = ['activity'];

            if (null !== $resource) {
                if (!$softDelete && !in_array($node->getResourceType()->getName(), $ignore)) {
                    $event = $this->lifeCycleManager->delete($node, $softDelete);

                    foreach ($event->getFiles() as $file) {
                        if ($softDelete) {
                            $parts = explode(
                              $this->filesDirectory.DIRECTORY_SEPARATOR,
                              $file
                          );

                            if (2 === count($parts)) {
                                $deleteDir = $this->filesDirectory.
                                  DIRECTORY_SEPARATOR.
                                  'DELETED_FILES';
                                $dest = $deleteDir.
                                  DIRECTORY_SEPARATOR.
                                  $parts[1];
                                $additionalDirs = explode(DIRECTORY_SEPARATOR, $parts[1]);

                                for ($i = 0; $i < count($additionalDirs) - 1; ++$i) {
                                    $deleteDir .= DIRECTORY_SEPARATOR.$additionalDirs[$i];
                                }

                                if (!is_dir($deleteDir)) {
                                    mkdir($deleteDir, 0777, true);
                                }
                                rename($file, $dest);
                            }
                        } else {
                            unlink($file);
                        }

                        //It won't work if a resource has no workspace for a reason or an other. This could be a source of bug.
                        $dir = $this->filesDirectory.
                          DIRECTORY_SEPARATOR.
                          'WORKSPACE_'.
                          $workspace->getId();

                        if (is_dir($dir) && $this->isDirectoryEmpty($dir)) {
                            rmdir($dir);
                        }
                    }
                }

                if ($softDelete) {
                    $node->setActive(false);
                    $this->om->persist($node);
                } else {
                    //for tags
                    // TODO : tags should directly listen to `resource.delete`
                    $this->dispatcher->dispatch(
                      'claroline_resources_delete',
                      'GenericData',
                      [[$node]]
                  );

                    /*
                     * If the child isn't removed here aswell, doctrine will fail to remove $resChild
                     * because it still has $resChild in its UnitOfWork or something (I have no idea
                     * how doctrine works tbh). So if you remove this line the suppression will
                     * not work for directory containing children.
                     */
                    $this->om->remove($resource);
                }
                //resource already doesn't exist anymore so we just remove everything
            } else {
                $this->om->remove($node);
                //for tags
                $this->dispatcher->dispatch(
                    'claroline_resources_delete',
                    'GenericData',
                    [[$node]]
                );
            }
        }

        $this->om->endFlushSuite();
    }

    public function preCopy(CopyEvent $event)
    {
        /** @var ResourceNode $node */
        $node = $event->getObject();
        /** @var ResourceNode $newNode */
        $newNode = $event->getCopy();

        $resource = $this->resourceManager->getResourceFromNode($node);

        if (!$resource) {
            //if something is malformed in production, try to not break everything if we don't need to. Just return null.
            return;
        }

        /** @var ResourceNode $newParent */
        $newParent = $event->getExtra()['parent'];
        $user = $event->getExtra()['user'];

        $newNode->setCreator($user);
        // link new node to its parent
        $newNode->setWorkspace($newParent->getWorkspace());
        $newNode->setParent($newParent);
        $newParent->addChild($newNode);

        /** @var AbstractResource $copy */
        $copy = $this->crud->copy($resource, [Options::REFRESH_UUID]);

        // link node and abstract resource
        $copy->setResourceNode($newNode);
        // unmapped but allow to retrieve it with the entity without any request for the following code
        $newNode->setResource($copy);

        $this->om->persist($newNode);
        $this->om->persist($copy);

        // TODO : this should not use a serializer internal method
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

        $user = $event->getExtra()['user'];

        // TODO : move this in the Directory listener
        if ('directory' === $node->getResourceType()->getName()) {
            // this is needed because otherwise I don't get the new node rights.
            // rights are directly created/updated in DB so the ResourceNode::getRights returns outdated data for now
            $this->om->refresh($newNode);

            foreach ($node->getChildren() as $child) {
                if ($child->isActive()) {
                    $this->crud->copy($child, [Options::NO_RIGHTS, Crud::NO_PERMISSIONS], ['user' => $user, 'parent' => $newNode]);
                }
            }
        }
    }

    private function isDirectoryEmpty(string $dirName): bool
    {
        $files = [];
        $dirHandle = opendir($dirName);

        if ($dirHandle) {
            while ($file = readdir($dirHandle)) {
                if ('.' !== $file && '..' !== $file) {
                    $files[] = $file;
                    break;
                }
            }
            closedir($dirHandle);
        }

        return 0 === count($files);
    }
}

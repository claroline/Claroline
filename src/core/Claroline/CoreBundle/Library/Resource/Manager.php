<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Resource\Event\ExportResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\DeleteResourceEvent;

class Manager
{

    /** @var EntityManager */
    private $em;
    /** @var FormFactory */
    private $formFactory;
    /** @var ContainerInterface */
    protected $container;
    /** @var EventDispatcher */
    private $ed;
    /** @var SecurityContext */
    private $sc;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->formFactory = $container->get('form.factory');
        $this->ed = $container->get('event_dispatcher');
        $this->sc = $container->get('security.context');
        $this->container = $container;
    }

    /**
     * Creates a resource. If instanceParentId is null, added to the root.
     *
     * @param integer          $parentInstanceId
     * @param integer          $workspaceId
     * @param AbstractResource $object
     * @param boolean          $instance the return type
     *
     * @return ResourceInstance | Resource
     *
     * @throws \Exception
     */
    public function create(AbstractResource $resource, $parentInstanceId, $resourceType, $returnInstance = true)
    {
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('type' => $resourceType));
        $user = $this->sc->getToken()->getUser();

        if (null !== $resource) {
            $ri = new ResourceInstance();
            $ri->setCreator($user);
            $dir = $this->em
                ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
                ->find($parentInstanceId);
            $ri->setParent($dir);
            $resource->setResourceType($resourceType);
            $ri->setWorkspace($dir->getWorkspace());
            $ri->setResource($resource);
            $ri->setName($resource->getName());
            $rename = $this->getUniqueName($ri, $dir);
            $ri->setName($rename);
            $this->em->persist($ri);
            $resource->setCreator($user);
            $this->em->persist($resource);
            $this->em->flush();

            return $returnInstance ? $ri : $resource;
        }

        throw \Exception("failed to create resource");
    }

    /**
     * Moves a resource instance.
     *
     * @param ResourceInstance  $child
     * @param ResourceInstance  $parent
     */
    public function move(ResourceInstance $child, ResourceInstance $parent)
    {
        $child->setWorkspace($parent->getWorkspace());
        $child->setParent($parent);
        $rename = $this->getUniqueName($child, $parent);
        $child->setName($rename);
        $this->em->flush();
    }

    /**
     * Removes a resource instance.
     *
     * @param ResourceInstance $resourceInstance
     */
    public function delete(ResourceInstance $resourceInstance)
    {
        if (1 === $resourceInstance->getResource()->getInstanceCount()) {

            if ($resourceInstance->getResourceType()->getType() !== 'directory') {
                $eventName = $this->normalizeEventName(
                    'delete', $resourceInstance->getResourceType()->getType()
                );
                $event = new DeleteResourceEvent(array($resourceInstance->getResource()));
                $this->ed->dispatch($eventName, $event);
            } else {
                $this->deleteDirectory($resourceInstance);
            }
        }

        $resourceInstance->getResource()->removeResourceInstance($resourceInstance);
        $this->em->remove($resourceInstance);
        $this->em->flush();
    }

    /**
     * Exports a resourc instance.
     *
     * @param ResourceInstance $resourceInstance
     *
     * @return file $item
     */
    public function export(ResourceInstance $resourceInstance)
    {
        if ('directory' != $resourceInstance->getResource()->getResourceType()->getType()) {
            $eventName = $this->normalizeEventName('export', $resourceInstance->getResource()->getResourceType()->getType());
            $event = new ExportResourceEvent($resourceInstance->getResource()->getId());
            $this->ed->dispatch($eventName, $event);
            $item = $event->getItem();
        } else {
            $archive = new \ZipArchive();
            $item = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->container->get('claroline.listener.file_listener')->generateGuid() . '.zip';
            $archive->open($item, \ZipArchive::CREATE);
            $this->addDirectoryToArchive($resourceInstance, $archive);
            $archive->close();
        }

        return $item;
    }

    /**
     * Adds a resource to a directory by reference.
     *
     * @param ResourceInstance $resourceInstance
     * @param ResourceInstance $parent
     */
    public function addToDirectoryByReference(ResourceInstance $resourceInstance, ResourceInstance $parent)
    {
        $resource = $resourceInstance->getResource();

        if ($resource->getShareType() == AbstractResource::PUBLIC_RESOURCE
            || $resource->getCreator() == $this->sc->getToken()->getUser()) {

           if ($resource->getResourceType()->getType() != 'directory') {
                $instanceCopy = $this->createReference($resource);
                $instanceCopy->setParent($parent);
                $instanceCopy->setWorkspace($parent->getWorkspace());
           } else {
                $instances = $resource->getResourceInstances();
                $instanceCopy = $this->createCopy($instances[0]);
                $instanceCopy->setParent($parent);
                $instanceCopy->setWorkspace($parent->getWorkspace());

                foreach ($instances[0]->getChildren() as $child) {
                    $this->addToDirectoryByReference($child, $instanceCopy);
                }
           }

           $this->em->persist($instanceCopy);
           $rename = $this->getUniqueName($resourceInstance, $parent);
           $instanceCopy->setName($rename);
           $this->em->persist($instanceCopy);
        }
    }

    public function normalizeEventName($prefix, $resourceType)
    {
        return $prefix . '_' . strtolower(str_replace(' ', '_', $resourceType));
    }

    /**
     * Returns an archive with the required content.
     *
     * @return file
     */
    public function multiExport($type)
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance');
        $archive = new \ZipArchive();
        $pathArch = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->container->get('claroline.listener.file_listener')->generateGuid() . '.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        switch ($type) {
            case 'classic': $instanceIds = $this->getClassicExportList($this->container->get('request')->query->all());
                break;
            case 'linker': $instanceIds = $this->getLinkerExportList($this->container->get('request')->query->all());
                break;
        }

        foreach ($instanceIds as $instanceId) {
            $instance = $repo->find($instanceId);

            if ($instance->getResource()->getResourceType()->getType() != 'directory') {

                $eventName = $this->normalizeEventName('export', $instance->getResource()->getResourceType()->getType());
                $event = new ExportResourceEvent($instance->getResource()->getId());
                $this->ed->dispatch($eventName, $event);
                $obj = $event->getItem();

                if ($obj != null) {
                    $archive->addFile($obj, $instance->getPath());
                }
            }
        }

        $archive->close();

        return file_get_contents($pathArch);
    }

    /**
     * Gets the list of the instances wich will be exported. The instanceIds array is given by the dynatree resource tree and
     * contains the minimal amount of informations to retrieve every resource needed.
     *
     * @param array $instanceIds
     *
     * @return array $toAppend
     */
    public function getClassicExportList($instanceIds)
    {
        $repoIns = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance');
        $dirIds = array();
        $resIds = array();

        foreach ($instanceIds as $instanceId) {
            $instance = $repoIns->find($instanceId);
            ($instance->getResource()->getResourceType()->getType() == 'directory') ? $dirIds[] = $instanceId : $resIds[] = $instanceId;
        }

        $toAppend = array();

        foreach ($dirIds as $dirId) {
            $found = false;
            foreach ($resIds as $resId) {
                $res = $repoIns->find($resId);

                if ($res->getRoot() == $dirId) {
                    $found = true;
                }
            }

            if (true != $found) {
                $directoryInstance = $repoIns->find($dirId);
                $children = $repoIns->children($directoryInstance, false);

                foreach ($children as $child) {
                    if ($child->getResource()->getResourceType()->getType() != 'directory') {
                        $toAppend[] = $child->getId();
                    }
                }
            }
        }

        return array_merge($toAppend, $resIds);
    }

    public function getLinkerExportList($instanceIds)
    {
        $repoIns = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance');
        $dirPseudoIds = array();
        $resIds = array();
        $toAppend = array();

        foreach ($instanceIds as $instanceId) {
             (true == is_numeric($instanceId) || true == is_int($instanceId)) ? $resIds[] = $instanceId : $dirPseudoIds[] = $instanceId;
        }

        foreach($dirPseudoIds as $dirPseudoId) {
            $split = explode('_', $dirPseudoId);
            $dir = $repoIns->find($split[1]);
            $resType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('type' => $split[0]));
            $instances = $repoIns->getChildrenInstanceList($dir, $resType);
            foreach ($instances as $instance) {
                $toAppend[] = $instance->getId();
            }
        }

        return array_merge($toAppend, $resIds);
    }

    private function createCopy(ResourceInstance $resourceInstance)
    {
        $user = $this->sc->getToken()->getUser();
        $ric = new ResourceInstance();
        $ric->setCreator($user);
        $this->em->flush();

        if ($resourceInstance->getResourceType()->getType()=='directory') {
            $resourceCopy = new Directory();
            $resourceCopy->setName($resourceInstance->getResource()->getName());
            $resourceCopy->setCreator($user);
            $resourceCopy->setResourceType($this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByType('directory'));
            $resourceCopy->addResourceInstance($ric);
        } else {
            $event = new CopyResourceEvent($resourceInstance->getResource());
            $eventName = $this->normalizeEventName('copy', $resourceInstance->getResourceType()->getType());
            $this->ed->dispatch($eventName, $event);
            $resourceCopy = $event->getCopy();
            $resourceCopy->setCreator($user);
            $resourceCopy->setResourceType($resourceInstance->getResourceType());
            $resourceCopy->addResourceInstance($ric);
        }

        $this->em->persist($resourceCopy);
        $ric->setResource($resourceCopy);

        return $ric;
    }

    private function createReference(AbstractResource $resource)
    {
        $ric = new ResourceInstance();
        $ric->setCreator($this->sc->getToken()->getUser());
        $ric->setResource($resource);
        $resource->addResourceInstance($ric);

        return $ric;
    }

    private function deleteDirectory($directoryInstance)
    {
        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($directoryInstance, false);
        foreach ($children as $child) {
            $rsrc = $child->getResource();
            if ($rsrc->getInstanceCount() === 1) {
                if ($child->getResourceType()->getType() == 'directory') {
                   $this->em->remove($rsrc);
                   $this->em->flush();
                } else {
                    $event = new DeleteResourceEvent(array($child->getResource()));
                    $this->ed->dispatch("delete_{$child->getResourceType()->getType()}", $event);
                    $this->em->flush();
                }
            }
        }

        $this->em->remove($directoryInstance->getResource());
    }

    private function addDirectoryToArchive($resourceInstance, $archive)
    {

        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, false);

        foreach ($children as $child) {
            if ($child->getResource()->getResourceType()->getType() != 'directory') {
                $eventName = $this->normalizeEventName('export', $child->getResource()->getResourceType()->getType());
                $event = new ExportResourceEvent($child->getResource()->getId());
                $this->ed->dispatch($eventName, $event);
                $obj = $event->getItem();

                if ($obj != null) {
                    $path = $this->getRelativePath($resourceInstance, $child, '');
                    $archive->addFile($obj, $resourceInstance->getName().DIRECTORY_SEPARATOR.$path . $child->getName());
                }
            }
        }

        $archive->addEmptyDir($resourceInstance->getResource()->getName());
    }

    private function getRelativePath(ResourceInstance $root, ResourceInstance $resourceInstance, $path)
    {
        if ($root != $resourceInstance->getParent()) {
            $path = $resourceInstance->getParent()->getName() . DIRECTORY_SEPARATOR . $path;
            $path = $this->getRelativePath($root, $resourceInstance->getParent(), $path);
        }

        return $path;
    }

    private function getUniqueName ($resourceInstance, $parent)
    {

        $children = $parent->getChildren();
        $name = $resourceInstance->getName();
        $arName = explode('~', pathinfo($name, PATHINFO_FILENAME));
        $baseName = $arName[0];
        $nbName = 0;

        if (null != $children) {
            foreach ($children as $child) {
                $childArName = explode('~', pathinfo($child->getName(), PATHINFO_FILENAME));
                $childBaseName = $childArName[0];
                if($childBaseName == $baseName && pathinfo($child->getName(),
                    PATHINFO_EXTENSION) == pathinfo($resourceInstance->getName(), PATHINFO_EXTENSION)) {
                    if(array_key_exists(1, $childArName)) {
                        $ind = $childArName[1];
                        if ($ind >= $nbName) {
                            $nbName = $ind;
                            $nbName++;
                        }
                    } else {
                        $nbName = 1;
                    }
                }
            }
        }
        if (0 != $nbName) {
            $newName = $baseName.'~'.$nbName.'.'.pathinfo($resourceInstance->getName(), PATHINFO_EXTENSION);
        } else {
            $newName = $resourceInstance->getName();
        }

        return $newName;
    }

}

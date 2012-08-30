<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Library\Resource\Utilities;
use Claroline\CoreBundle\Library\Resource\Event\ExportResourceEvent;

class Exporter
{

    /* @var EntityManager */
    private $em;
    /* @var EventDispatcher */
    private $ed;
    /* @var Utilities */
    private $ut;

    public function __construct(EntityManager $em, EventDispatcher $ed, Utilities $ut)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->ut = $ut;
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
            $eventName = $this->ut->normalizeEventName('export', $resourceInstance->getResource()->getResourceType()->getType());
            $event = new ExportResourceEvent($resourceInstance->getResource()->getId());
            $this->ed->dispatch($eventName, $event);
            $item = $event->getItem();
        } else {
            $archive = new \ZipArchive();
            $item = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->ut->generateGuid() . '.zip';
            $archive->open($item, \ZipArchive::CREATE);
            $this->addDirectoryToArchive($resourceInstance, $archive);
            $archive->close();
        }

        return $item;
    }

    /**
     * Returns an archive with the required content.
     *
     * @return file
     */
    public function multiExport($ids)
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance');
        $archive = new \ZipArchive();
        $pathArch = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->ut->generateGuid() . '.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        $instanceIds = $this->getClassicExportList($ids);

        if ($instanceIds == null) {
            throw new \LogicException("You must select some resources to export.");
        }

        foreach ($instanceIds as $instanceId) {
            $instance = $repo->find($instanceId);

            if ($instance->getResource()->getResourceType()->getType() != 'directory') {

                $eventName = $this->ut->normalizeEventName('export', $instance->getResource()->getResourceType()->getType());
                $event = new ExportResourceEvent($instance->getResource()->getId());
                $this->ed->dispatch($eventName, $event);
                $obj = $event->getItem();

                if ($obj != null) {
                    $archive->addFile($obj, $instance->getPath());
                }
            } else {
                $archive->addEmptyDir($instance->getName());
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


        $merge = array_merge($toAppend, $resIds);
        $merge = array_merge($merge, $dirIds);

        return $merge;
    }

    /**
     * Adds a directory in a zip archive.
     *
     * @param ResourceInstance $resourceInstance
     * @param ZipArchive $archive
     */
    private function addDirectoryToArchive(ResourceInstance $resourceInstance, \ZipArchive $archive)
    {
        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, false);
        $archive->addEmptyDir($resourceInstance->getName());

        foreach ($children as $child) {
            if ($child->getResource()->getResourceType()->getType() != 'directory') {
                $eventName = $this->ut->normalizeEventName('export', $child->getResource()->getResourceType()->getType());
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

    /**
     * Gets the relative path between 2 instances (not optimized yet).
     *
     * @param ResourceInstance $root
     * @param ResourceInstance $resourceInstance
     * @param string $path
     *
     * @return string
     */
    private function getRelativePath(ResourceInstance $root, ResourceInstance $resourceInstance, $path)
    {
        if ($root != $resourceInstance->getParent()) {
            $path = $resourceInstance->getParent()->getName() . DIRECTORY_SEPARATOR . $path;
            $path = $this->getRelativePath($root, $resourceInstance->getParent(), $path);
        }

        return $path;
    }
}

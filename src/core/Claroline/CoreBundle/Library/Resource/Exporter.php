<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Library\Resource\Utilities;
use Claroline\CoreBundle\Library\Resource\Event\ExportResourceEvent;
use Claroline\CoreBundle\Library\Logger\Event\ResourceLoggerEvent;

class Exporter
{

    /* @var EntityManager */
    private $em;
    /* @var EventDispatcher */
    private $ed;
    /* @var Utilities */
    private $ut;
    /* @var SecurityContext */
    private $sc;

    public function __construct(EntityManager $em, EventDispatcher $ed, Utilities $ut, $sc)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->ut = $ut;
        $this->sc = $sc;
    }

    /**
     * Returns an archive with the required content.
     *
     * @return file
     */
    public function exportResources($ids)
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $archive = new \ZipArchive();
        $pathArch = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->ut->generateGuid() . '.zip';
        $archive->open($pathArch, \ZipArchive::CREATE);
        $resourceIds = $this->expandResourceIds($ids);

        if ($resourceIds == null) {
            throw new \LogicException("You must select some resources to export.");
        }
        $currentDir = $repo->find($ids[0])->getParent();

        foreach ($resourceIds as $resourceId) {
            $resource = $repo->find($resourceId);

            if (get_class($resource) == 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
                $resource = $resource->getResource();
            }
            
            if ($resource->getResourceType()->getName() != 'directory') {

                $eventName = $this->ut->normalizeEventName('export', $resource->getResourceType()->getName());
                $event = new ExportResourceEvent($resource);
                $this->ed->dispatch($eventName, $event);
                $obj = $event->getItem();

                if ($obj != null) {
                    $archive->addFile($obj, $this->getRelativePath($currentDir, $resource) . $resource->getName());
                } else {
                     $archive->addFromString($this->getRelativePath($currentDir, $resource) . $resource->getName(), '');
                }
            } else {
                $archive->addEmptyDir($this->getRelativePath($currentDir, $resource). $resource->getName());
            }
            $event = new ResourceLoggerEvent(
                $resource,
                ResourceLoggerEvent::EXPORT_ACTION
            );
            $this->ed->dispatch('log_resource', $event);
        }

        $archive->close();
//        throw new \Exception('coucou');
        return $pathArch;
    }

    /**
     * Add the list of the resource under the given IDs (if they are directories)
     * to the given list of resourceIds.
     *
     * @param array $resourceIds List of resources to retrieve.
     *
     * @return array $toAppend
     */
    public function expandResourceIds($resourceIds)
    {
        $repoIns = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $dirIds = array();
        $resIds = array();

        foreach ($resourceIds as $resourceId) {
            $resource = $repoIns->find($resourceId);
            ($resource->getResourceType()->getName() == 'directory') ? $dirIds[] = $resourceId : $resIds[] = $resourceId;
        }

        $toAppend = array();

        foreach ($dirIds as $dirId) {
            $directory = $repoIns->find($dirId);
            $children = $repoIns->getChildren($directory, false);

            foreach ($children as $child) {
                if ($child->getResourceType()->getName() != 'directory' && $child->getResourceType()->isVisible()) {
                    $toAppend[] = $child->getId();
                }
            }
        }

        $merge = array_merge($toAppend, $resIds);
        $merge = array_merge($merge, $dirIds);

        return $merge;
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
    private function getRelativePath($root, AbstractResource $resource, $path = '')
    {
        if ($root != $resource->getParent()) {
            $path = $resource->getParent()->getName() . DIRECTORY_SEPARATOR . $path;
            $path = $this->getRelativePath($root, $resource->getParent(), $path);
        }

        return $path;
    }
}

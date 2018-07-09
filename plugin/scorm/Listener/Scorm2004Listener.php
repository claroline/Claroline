<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Listener;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service
 */
class Scorm2004Listener
{
    // path to the Scorm archive file
    private $filePath;
    private $om;
    private $scormResourceRepo;
    // path to the Scorm unzipped files
    private $scormResourcesPath;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "om"        = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ContainerInterface $container, ObjectManager $om)
    {
        $this->filePath = $container->getParameter('claroline.param.files_directory');
        $this->om = $om;
        $this->scormResourceRepo = $om->getRepository('ClarolineScormBundle:Scorm2004Resource');
        $this->scormResourcesPath = $container->getParameter('claroline.param.uploads_directory').
            DIRECTORY_SEPARATOR.
            'scormresources'.
            DIRECTORY_SEPARATOR;
    }

    /**
     * @DI\Observe("delete_claroline_scorm_2004")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $hashName = $event->getResource()->getHashName();
        $scormArchiveFile = $this->filePath.DIRECTORY_SEPARATOR.$hashName;
        $scormResourcesPath = $this->scormResourcesPath.$hashName;

        $nbScorm = (int) ($this->scormResourceRepo->getNbScormWithHashName($hashName));

        if (1 === $nbScorm) {
            if (file_exists($scormArchiveFile)) {
                $event->setFiles([$scormArchiveFile]);
            }
            if (file_exists($scormResourcesPath)) {
                try {
                    $this->deleteFiles($scormResourcesPath);
                } catch (\Exception $e) {
                }
            }
        }
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * Deletes recursively a directory and its content.
     *
     * @param $dirPath The path to the directory to delete
     */
    private function deleteFiles($dirPath)
    {
        foreach (glob($dirPath.DIRECTORY_SEPARATOR.'{*,.[!.]*,..?*}', GLOB_BRACE) as $content) {
            if (is_dir($content)) {
                $this->deleteFiles($content);
            } else {
                unlink($content);
            }
        }
        rmdir($dirPath);
    }
}

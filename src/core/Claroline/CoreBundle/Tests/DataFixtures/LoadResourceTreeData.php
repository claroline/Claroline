<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\LoggableFixture;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;


class LoadResourceTreeData extends LoggableFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;

    private $username;
    private $depth;
    private $directoryCount;
    private $fileCount;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function __construct($username, $depth, $directoryCount, $fileCount)
    {
        $this->username = $username;
        $this->depth = $depth;
        $this->directoriesCount = $directoryCount;
        $this->filesCount = $fileCount;
        $this->cptDirectories = 0;
        $this->cptFiles = 0;

        $this->dirNames = array(
            'my files',
            'courses',
            'lessons',
            'misc',
            'private',
            'public',
            'documents'
        );

        $this->dirNamesOffset = count($this->dirNames);
        $this->dirNamesOffset--;

        $this->fileNames = array(
            'video.mp4',
            'video.mov',
            'video.flv',
            'pdf.pdf',
            'office.odt',
            'text.txt',
            'image.png',
            'image.jpg',
            'gif.gif'
        );

        $this->fileNamesOffset = count($this->fileNames);
        $this->fileNamesOffset--;
    }

    public function load(ObjectManager $manager)
    {
        $numTot = (( 1 - pow($this->directoriesCount, $this->depth + 1) ) / (1 - $this->directoriesCount) ) - 1;
        $this->log("Number of directories that will be generated: " . $numTot);
        $this->log("Number of files that will be generated: " . $numTot * $this->filesCount);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->user = $em->getRepository('Claroline\CoreBundle\Entity\User')->findOneBy(array('username' => $this->username));
        $this->workspace = $this->user->getPersonalWorkspace();
        $this->userRootDirectory = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->findOneBy(array('parent' => null, 'workspace' => $this->user->getPersonalWorkspace()->getId()));
        $this->dirType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'directory'));
        $this->fileType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'file'));

        $this->generateItems($manager, $this->depth, 0, $this->directoriesCount, $this->filesCount, $this->userRootDirectory);
        $em->flush();

        $this->log("\n===> NUMBER OF DIR CREATED: " . $this->cptDirectories);
        $this->log("\n===> NUMBER OF FILES CREATED: " . $this->cptFiles);
    }

    private function generateItems($om, $maxDepth, $curDepth, $directoryCount, $fileCount, $parent)
    {
        $curDepth++;

        $dirToBeDetached = array();
        for ($j = 0; $j < $directoryCount; $j++) {

            $ri = $this->addDirectory($om, $curDepth, microtime()."-".$this->dirNames[rand(0, $this->dirNamesOffset)], $parent);
            $dirToBeDetached[] = $ri;
            $this->cptDirectories++;
            $filesToBeDetached = array();
            for ($k = 0; $k < $fileCount; $k++) {
                $fi = $this->addFile($om, $curDepth, microtime()."-".$this->fileNames[rand(0, $this->fileNamesOffset)], $ri);
                $filesToBeDetached[] = $fi;
                $this->cptFiles++;
            }
            $this->log("Depth: " . $curDepth . " => Flushing... (files: " . $this->cptFiles . ", directories: " . $this->cptDirectories . ")");
            $om->flush();
            // Detach file entities for better perfs as we do not need them anymore.
            foreach ($filesToBeDetached as $fi) {
                $om->detach($fi);
            }

            if ($curDepth < $maxDepth) {
                $this->generateItems($om, $maxDepth, $curDepth, $directoryCount, $fileCount, $ri);

                if ($curDepth == 1) {
                    // Clear the EntityManager (EM) to free memory and speed all EM operations.
                    // We may clear the EM only when coming back at level 1 else we have
                    // problems with entities needed in the hierarchy.
                    $om->clear();
                    // Re-attach all needed entities else we have problems later.
                    $this->userRootDirectory = $om->merge($this->userRootDirectory);
                    $this->user = $om->merge($this->user);
                    $this->workspace = $om->merge($this->workspace);
                    $this->dirType = $om->merge($this->dirType);
                    $this->fileType = $om->merge($this->fileType);
                    $parent = $om->merge($parent);
                }
            }
        }
        // Detach directory entities for better perfs as we do not need them anymore.
        foreach ($dirToBeDetached as $dir) {
            $om->detach($dir);
        }
        echo " [UOW size: " . $om->getUnitOfWork()->size() . "]";
    }

    private function addDirectory($om, $depth, $name, $parent)
    {
        $dir = new Directory();
        $dir->setResourceType($this->dirType);
        $dir->setCreator($this->user);
        $ri = new ResourceInstance();
        $ri->setCreator($this->user);
        $ri->setResource($dir);
        $ri->setName($name);
        $ri->setParent($parent);
        $ri->setWorkspace($this->workspace);
        $dir = $this->getContainer()->get('claroline.resource.icon_creator')->setResourceIcon($dir, $this->dirType);
        $om->persist($dir);
        $om->persist($ri);
//        echo str_repeat("   ", $depth)."   ADDING DIRECTORY $name \n";

        return $ri;
    }

    private function addFile($om, $depth, $name, $parent)
    {
        $file = tempnam($this->getContainer()->getParameter('claroline.files.directory'), 'tmpfile');
        $hash = pathinfo($file, PATHINFO_FILENAME);
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setCreator($this->user);
        $file->setHashName($hash);
        $file->setSize(0);
        $file->setMimeType("plain/text");
        $file = $this->getContainer()->get('claroline.resource.icon_creator')->setResourceIcon($file, $this->getMimeType($name), true);
        $ri = new ResourceInstance();
        $ri->setCreator($this->user);
        $ri->setResource($file);
        $ri->setName($name);
        $ri->setParent($parent);
        $ri->setWorkspace($this->workspace);
        $om->persist($file);
        $om->persist($ri);
//        echo str_repeat("   ", $depth)."    adding file $name \n";

        return $ri;
    }

    private function getMimeType($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        $mimeTypes = array();
        $mimeTypes['mov'] = 'video/mov';
        $mimeTypes['flv'] = 'video/flv';
        $mimeTypes['mp4'] = 'video/mp4';
        $mimeTypes['pdf'] = 'application/pdf';
        $mimeTypes['odt'] = 'application/vnd.oasis.opendocument.text';
        $mimeTypes['txt'] = 'plain/text';
        $mimeTypes['png'] = 'image/png';
        $mimeTypes['jpg'] = 'image/jpg';
        $mimeTypes['gif'] = 'image/gif';

        return $mimeTypes[$ext];
    }
}

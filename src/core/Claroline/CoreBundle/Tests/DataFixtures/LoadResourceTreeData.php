<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;


class LoadResourceTreeData extends LoggableFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    private $username;
    private $depth;
    private $directoryCount;
    private $fileCount;

    public function __construct($username, $depth, $directoryCount, $fileCount)
    {
        $this->username = $username;
        $this->depth = $depth;
        $this->directoryCount = $directoryCount;
        $this->fileCount = $fileCount;
        $this->cptDirectories = 0;
        $this->cptFiles = 0;
        $this->dirNameMasks = array(
            'My files - %s',
            'Courses - %s',
            'Lessons - %s',
            'Misc - %s',
            'Private - %s',
            'Public - %s',
            'Documents - %s'
        );
        $this->fileNameMasks = array(
            'video-%s.mp4'  => 'video/mp4',
            'video-%s.mov' => 'video/mov',
            'video-%s.flv' => 'video/flv',
            'document-%s.pdf' => 'application/pdf',
            'document-%s.odt' => 'application/vnd.oasis.opendocument.text',
            'text-%s.txt' => 'plain/text',
            'image-%s.png' => 'image/png',
            'image-%s.jpg' => 'image/jpg',
            'image-%s.gif' => 'gif/gif'
        );
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function load(ObjectManager $manager)
    {
        $numTot = (( 1 - pow($this->directoryCount, $this->depth + 1) ) / (1 - $this->directoryCount) ) - 1;
        $this->log('Number of directories that will be generated: ' . $numTot);
        $this->log('Number of files that will be generated: ' . $numTot * $this->fileCount);
        $this->user = $manager->getRepository('Claroline\CoreBundle\Entity\User')
            ->findOneBy(array('username' => $this->username));
        $this->workspace = $this->user->getPersonalWorkspace();
        $this->userRootDirectory = $manager->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->findOneBy(array('parent' => null, 'workspace' => $this->user->getPersonalWorkspace()->getId()));
        $this->dirType = $manager->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('name' => 'directory'));
        $this->fileType = $manager->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('name' => 'file'));
        $nextId = $manager
            ->createQuery('SELECT MAX(i.id) + 1 FROM Claroline\CoreBundle\Entity\Resource\AbstractResource i')
            ->getSingleResult();
        $this->generateItems(
            $manager,
            $this->depth, 0,
            $this->directoryCount,
            $this->fileCount,
            $this->userRootDirectory,
            array_shift($nextId)
        );
        $manager->flush();
        $this->log('===> NUMBER OF DIR CREATED: ' . $this->cptDirectories);
        $this->log('===> NUMBER OF FILES CREATED: ' . $this->cptFiles);
    }

    private function generateItems($em, $maxDepth, $curDepth, $directoryCount, $fileCount, $parent, $nextId)
    {
        $curDepth++;
        $dirToBeDetached = array();

        for ($j = 0; $j < $directoryCount; $j++) {
            $ri = $this->addDirectory($em,
                $curDepth,
                sprintf($this->dirNameMasks[array_rand($this->dirNameMasks)], $nextId),
                $parent
            );
            $dirToBeDetached[] = $ri;
            $this->cptDirectories++;
            $nextId++;
            $filesToBeDetached = array();

            for ($k = 0; $k < $fileCount; $k++) {
                $fileNameMask = array_rand($this->fileNameMasks);
                $fi = $this->addFile($em,
                    $curDepth,
                    sprintf($fileNameMask, $nextId), $this->fileNameMasks[$fileNameMask],
                    $ri
                );
                $filesToBeDetached[] = $fi;
                $this->cptFiles++;
                $nextId++;
            }

            $this->log('Depth: ' . $curDepth . ' => Flushing... (files: ' . $this->cptFiles
                . ", directories: " . $this->cptDirectories . ')'
            );
            $em->flush();

            // Detach file entities for better perfs as we do not need them anymore.
            foreach ($filesToBeDetached as $fi) {
                $em->detach($fi);
            }

            if ($curDepth < $maxDepth) {
                $this->generateItems($em, $maxDepth, $curDepth, $directoryCount, $fileCount, $ri, $nextId);

                if ($curDepth == 1) {
                    // Clear the EntityManager (EM) to free memory and speed all EM operations.
                    // We may clear the EM only when coming back at level 1 else we have
                    // problems with entities needed in the hierarchy.
                    $em->clear();
                    // Re-attach all needed entities else we have problems later.
                    $this->userRootDirectory = $em->merge($this->userRootDirectory);
                    $this->user = $em->merge($this->user);
                    $this->workspace = $em->merge($this->workspace);
                    $this->dirType = $em->merge($this->dirType);
                    $this->fileType = $em->merge($this->fileType);
                    $parent = $em->merge($parent);
                }
            }
        }

        // Detach directory entities for better perfs as we do not need them anymore.
        foreach ($dirToBeDetached as $dir) {
            $em->detach($dir);
        }

        $this->log(' [UOW size: ' . $em->getUnitOfWork()->size() . ']');
    }

    private function addDirectory($em, $depth, $name, $parent)
    {
        $dir = new Directory();
        $dir->setResourceType($this->dirType);
        $dir->setCreator($this->user);
        $dir->setName($name);
        $dir->setParent($parent);
        $dir->setWorkspace($this->workspace);
        $dir = $this->getContainer()->get('claroline.resource.icon_creator')->setResourceIcon($dir);
        $em->persist($dir);

        return $dir;
    }

    private function addFile($em, $depth, $name, $mimeType, $parent)
    {
        $file = tempnam($this->getContainer()->getParameter('claroline.files.directory'), 'tmpfile');
        $hash = pathinfo($file, PATHINFO_FILENAME);
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setCreator($this->user);
        $file->setHashName($hash);
        $file->setSize(0);
        $file->setMimeType($mimeType);
        $file = $this->getContainer()->get('claroline.resource.icon_creator')->setResourceIcon($file, true);
        $file->setName($name);
        $file->setParent($parent);
        $file->setWorkspace($this->workspace);
        $em->persist($file);

        return $file;
    }
}

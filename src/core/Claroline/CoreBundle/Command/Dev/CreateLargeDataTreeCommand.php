<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;

class CreateLargeDataTreeCommand extends ContainerAwareCommand
{

    public function __construct()
    {
        parent::__construct();
        $this->directoryCount = 0;
        $this->fileCount = 0;

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

    protected function configure()
    {
        $this->setName('claroline:datatree:create')
            ->setDescription('Creates a new data tree of resource instances. For better perfs, launch with --env=prod.');
        $this->setDefinition(array(
            new InputArgument('username', InputArgument::REQUIRED, 'The user creating the tree'),
            new InputArgument('depth', InputArgument::REQUIRED, 'The number of level'),
            new InputArgument('directory_count', InputArgument::REQUIRED, 'The number of directories per level (min 1)'),
            new InputArgument('file_count', InputArgument::REQUIRED, 'The number of files per level'),
        ));
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'username' => 'username',
            'depth' => 'depth',
            'directory_count' => 'number of directories per level',
            'file_count' => 'number of files per level'
        );

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument, $this->askArgument($output, $argumentName)
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output, "Enter the {$argumentName}: ", function($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $maxDepth = $input->getArgument('depth');
        $directoryCount = $input->getArgument('directory_count');
        $fileCount = $input->getArgument('file_count');

        $numTot = (( 1 - pow($directoryCount, $maxDepth + 1) ) / (1 - $directoryCount) ) - 1;
        $output->writeln("Number of directories that will be generated: " . $numTot);
        $output->writeln("Number of files that will be generated: " . $numTot * $fileCount);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->user = $em->getRepository('Claroline\CoreBundle\Entity\User')->findOneBy(array('username' => $username));
        $this->workspace = $this->user->getPersonalWorkspace();
        $this->userRootDirectory = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->findOneBy(array('parent' => null, 'workspace' => $this->user->getPersonalWorkspace()->getId()));
        $this->dirType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'directory'));
        $this->fileType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'file'));

        $this->generateItems($em, $maxDepth, 0, $directoryCount, $fileCount, $this->userRootDirectory);
        $em->flush();

        $output->writeln("\n===> NUMBER OF DIR CREATED: " . $this->directoryCount);
        $output->writeln("\n===> NUMBER OF FILES CREATED: " . $this->filesCount);
    }

    private function generateItems($em, $maxDepth, $curDepth, $directoryCount, $fileCount, $parent)
    {
        $curDepth++;

        $dirToBeDetached = array();
        for ($j = 0; $j < $directoryCount; $j++) {

            $ri = $this->addDirectory($em, $curDepth, microtime()."-".$this->dirNames[rand(0, $this->dirNamesOffset)], $parent);
            $dirToBeDetached[] = $ri;
            $this->directoryCount++;
            $filesToBeDetached = array();
            for ($k = 0; $k < $fileCount; $k++) {
                $fi = $this->addFile($em, $curDepth, microtime()."-".$this->fileNames[rand(0, $this->fileNamesOffset)], $ri);
                $filesToBeDetached[] = $fi;
                $this->filesCount++;
            }
            echo "\n Depth: " . $curDepth . " => Flushing... (files: " . $this->filesCount . ", directories: " . $this->directoryCount . ")";
            $em->flush();
            // Detach file entities for better perfs as we do not need them anymore.
            foreach ($filesToBeDetached as $fi) {
                $em->detach($fi);
            }

            if ($curDepth < $maxDepth) {
                $this->generateItems($em, $maxDepth, $curDepth, $directoryCount, $fileCount, $ri);

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
        echo " [UOW size: " . $em->getUnitOfWork()->size() . "]";
    }

    private function addDirectory($em, $depth, $name, $parent)
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
        $em->persist($dir);
        $em->persist($ri);
//        echo str_repeat("   ", $depth)."   ADDING DIRECTORY $name \n";

        return $ri;
    }

    private function addFile($em, $depth, $name, $parent)
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
        $em->persist($file);
        $em->persist($ri);
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
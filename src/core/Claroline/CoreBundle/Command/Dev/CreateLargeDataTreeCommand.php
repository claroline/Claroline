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
    var $directoryCount = 0;
    var $fileCount = 0;

    protected function configure()
    {
        $this->setName('claroline:datatree:create')
            ->setDescription('Creates a new data tree.');
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
        $depth = $input->getArgument('depth');
        $directoryCount = $input->getArgument('directory_count');
        $fileCount = $input->getArgument('file_count');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('Claroline\CoreBundle\Entity\User')->findOneBy(array('username'=> $username));
        $userRootDirectory = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->findOneBy(array ('parent' => null, 'workspace' => $user->getPersonalWorkspace()->getId()));

        $this->generateItems($depth, $directoryCount, $fileCount, $userRootDirectory, $user);
        echo "\n+++++++++++++++++++ FILE +++++++++++++++++++++++: $this->filesCount";
        echo "\n+++++++++++++++++++ DIR +++++++++++++++++++++++: $this->directoryCount";
        $em->flush();

    }

    private function generateItems($depth, $directoryCount, $fileCount, $parent, $user)
    {
        if ($depth <= 0) {
            return;
        }
        $depth--;
        $dirType = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'directory'));
        $fileType = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'file'));

        for ($j = 0; $j < $directoryCount; $j++) {
            $ri = $this->addDirectory($this->generateGuid(), $parent, $dirType, $user);
            $this->directoryCount++;
            if ($this->directoryCount%100 === 0) {
                $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
            }
            for ($k = 0; $k < $fileCount; $k++) {
                $this->addFile($this->generateGuid(), $ri, $fileType, $user);
                $this->filesCount++;
                if ($this->filesCount%100 === 0) {
                    $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
                }
            }

            echo($depth.' - '.$j.'\n');
            $this->generateItems($depth, $directoryCount, $fileCount, $ri, $user);
        }


    }

    private function addDirectory($name, $parent, $dirType, $user)
    {
        $dir = new Directory();
        $dir->setResourceType($dirType);
        $dir->setCreator($user);
        $ri = new ResourceInstance();
        $ri->setCreator($user);
        $ri->setResource($dir);
        $ri->setName($name);
        $ri->setParent($parent);
        $ri->setWorkspace($parent->getWorkspace());
        $em = $this->getContainer()->get('doctrine.orm.entity_manager')->persist($dir);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager')->persist($ri);
        echo "addDirectory $name \n";

        return $ri;
    }

    private function addFile($name, $parent, $fileType, $user)
    {
        $file = new File();
        $file->setResourceType($fileType);
        $file->setCreator($user);
        $file->setHashName($this->generateGuid());
        $file->setSize(0);
        $ri = new ResourceInstance();
        $ri->setCreator($user);
        $ri->setResource($file);
        $ri->setName($name);
        $ri->setParent($parent);
        $ri->setWorkspace($parent->getWorkspace());
        $em = $this->getContainer()->get('doctrine.orm.entity_manager')->persist($file);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager')->persist($ri);
        echo "addFile $name \n";

        return $ri;
    }

    private function generateGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }

}
<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class LoadFileData extends AbstractFixture implements ContainerAwareInterface
{
    private $creator;
    private $directory;
    private $files;
    private $container;

    /**
     * Constructor. Expects the username of the creator of the file(s), the
     * name of the directory where the file(s) should be created -- i.e. a virtual
     * directory bound to a workspace -- and an array of files to create.
     *
     * Both the username and the directory name must have been loaded in a previous
     * fixture and been referenced with 'user/[username]' and 'directory/[directory]'
     * labels.
     *
     * If any of the files to be recorded doesn't actually exist as a physical file,
     * it will be created. Otherwise the original file will simply be copied. That means
     * that both "fake" files (e.g 'foo.txt') and real files (e.g '/path/to/a/real/file')
     * are accepted as arguments.
     *
     * Each created file will be referenced with a 'file/[file name]' label.
     *
     * @param string    $creator    Username of the creator of the file(s)
     * @param string    $directory  Name of the directory where the file(s) should be created
     * @param array     $files      File names (or paths) to be created
     */
    public function __construct($creator, $directory, array $files)
    {
        $this->creator = $creator;
        $this->directory = $directory;
        $this->files = $files;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $this->getReference("user/{$this->creator}");
        $directory = $this->getReference("directory/{$this->directory}");
        $resourceManager = $this->container->get('claroline.resource.manager');
        $resourceUtilities = $this->container->get('claroline.resource.utilities');
        $filesDirectory = $this->container->getParameter('claroline.param.files_directory');

        foreach ($this->files as $filePath) {
            $filePathParts = explode(DIRECTORY_SEPARATOR, $filePath);
            $fileName = array_pop($filePathParts);
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $hashName = "{$resourceUtilities->generateGuid()}.{$extension}";
            $targetFilePath = $filesDirectory . DIRECTORY_SEPARATOR . $hashName;
            $file = new File();
            $file->setName($fileName);
            $file->setHashName($hashName);

            if (file_exists($filePath)) {
                copy($filePath, $targetFilePath);
                $file->setSize(filesize($filePath));
            } else {
                touch($targetFilePath);
                $file->setSize(0);
            }

            $mimeType = MimeTypeGuesser::getInstance()->guess($targetFilePath);
            $file->setMimeType($mimeType);
            $resourceManager->create($file, $directory->getId(), 'file', $user);
            $this->addReference("file/{$fileName}", $file);
        }

        $manager->flush();
    }
}
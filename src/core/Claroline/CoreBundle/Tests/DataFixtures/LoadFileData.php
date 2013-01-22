<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\File;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class LoadFileData extends AbstractFixture implements ContainerAwareInterface
{
    private $name;
    private $parent;
    private $user;
    private $fileName;
    private $lastFileCreated;
    protected $container;

    public function __construct($name, AbstractResource $parent, User $user, $fileName)
    {
        $this->name = $name;
        $this->parent = $parent;
        $this->user = $user;
        $this->fileName = $fileName;
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
        $ds = DIRECTORY_SEPARATOR;
        $file = new File();
        $extension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $size = filesize($this->fileName);
        $mimeType = MimeTypeGuesser::getInstance()->guess($this->fileName);
        $hashName = $this->getContainer()->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
        copy($this->fileName, "{$this->getContainer()->getParameter('claroline.files.directory')}{$ds}{$hashName}");
        $file->setSize($size);
        $file->setName($this->name);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);
        $this->lastFileCreated = $this->getContainer()
            ->get('claroline.resource.manager')
            ->create($file, $this->parent->getId(), 'file', $this->user);
    }

    public function getLastFileCreated()
    {
        return $this->lastFileCreated;
    }
}

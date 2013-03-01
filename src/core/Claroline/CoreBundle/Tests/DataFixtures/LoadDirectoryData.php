<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadDirectoryData extends AbstractFixture implements ContainerAwareInterface
{
    private $creator;
    private $paths;
    private $container;

    /**
     * Constructor. Expects a creator username and an array of directory paths (e.g.
     * dir1/dir2, dir1/dir3/dir4, ...). The root directory of each path must be the
     * name of an already referenced workspace root directory. Other directories
     * included in the path are created if needed.
     *
     * Each directory will be referenced with a 'directory/[directory name]' label.
     *
     * @param string    $creator    Username of the creator of the resource
     * @param array     $paths              Directory paths to be created
     */
    public function __construct($creator, array $paths)
    {
        $this->creator = $creator;
        $this->paths = $paths;
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
        $resourceManager = $this->container->get('claroline.resource.manager');

        foreach ($this->paths as $path) {
            $directories = explode('/', $path);

            for ($i = 0, $dirCount = count($directories); $i < $dirCount; ++$i) {
                if ($i > 0) {
                    if (!$this->hasReference("directory/{$directories[$i]}")) {
                        $directory = new Directory();
                        $directory->setName($directories[$i]);
                        $parent = $this->getReference("directory/{$directories[$i - 1]}");
                        $resourceManager->create($directory, $parent->getId(), 'directory', $user);
                        $this->addReference("directory/{$directories[$i]}", $directory);
                    }
                }
            }
        }
    }
}
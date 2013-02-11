<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadDirectoryData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /*
     * Create a directory tree for 5 users, 5 ws_creators and 5 admins
     */
    /** @var ContainerInterface $container */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->createTree($this->getReference('user/user'), 10, $manager);
        $manager->flush();
    }

    protected function createDirectory($parent, $directoryName, $user, ObjectManager $manager)
    {
        $directory = new Directory();
        $directory->setName($directoryName);
        $directory->setParent($parent);
        $directory->setCreator($user);
        $directory->setResourceType($this->getReference('resource_type/directory'));
        $this->addReference("directory/{$directory->getName()}", $directory);
        $manager->persist($directory);

        return $directory;
    }
    /* The tree is created this way:
     *
     * amount = 3
     *
     * ROOT
     *    DIR1
     *    DIR2
     *
     * amount = 4
     *
     * ROOT
     *    DIR1
     *    DIR2
     *       DIR3
     *
     * amount = 5
     *
     * ROOT
     *    DIR1
     *    DIR2
     *       DIR3
     *       DIR4
     *
     * amount = 6
     *
     * ROOT
     *    DIR1
     *    DIR2
     *       DIR3
     *       DIR4
     *          DIR5
     *
     * etc...
     */

    protected function createTree($user, $amount, ObjectManager $manager)
    {
        $name = "DIR_ROOT_{$user->getUsername()}";
        $root = $this->createDirectory(null, $name, $user, $manager);
        ;
        $lastref = $root;

        for ($i = 1; $i < ($amount); $i++) {
            $name = "DIR_{$i}_{$user->getUsername()}";
            $directory = $this->createDirectory($lastref, $name, $user, $manager);
            if ($i % 2 == 0) {
                $lastref = $directory;
            }
        }
    }

    public function getOrder()
    {
        return 9;
    }
}
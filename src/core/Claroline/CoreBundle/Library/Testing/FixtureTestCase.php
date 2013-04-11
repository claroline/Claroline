<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\DataFixtures\LoadPlatformRolesData;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadGroupData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadRoleData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadUserData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class FixtureTestCase extends TransactionalTestCase
{
    /** @var EntityManager */
    protected $em;

    /** @var ReferenceRepository */
    private $referenceRepo;

    /**
     * Initializes the entity manager and the fixture reference repository.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->referenceRepo = new ReferenceRepository($this->em);
    }

    /**
     * Cleans the test files directories.
     */
    protected function tearDown()
    {
        $cleanTestFilesDirectories = function (array $directories) {
            foreach ($directories as $directory) {
                $iterator = new \DirectoryIterator($directory);

                foreach ($iterator as $item) {
                    if ($item->isFile() && $item->getFileName() !== 'placeholder') {
                        chmod($item->getPathname(), 0777);
                        unlink($item->getPathname());
                    }
                }
            }
        };
        $container = $this->client->getContainer();
        $cleanTestFilesDirectories(
            array(
                $container->getParameter('claroline.param.files_directory'),
                $container->getParameter('claroline.param.thumbnails_directory'),
            )
        );
        parent::tearDown();
    }

    /**
     * Loads a fixture, injecting the entity manager, the reference repository
     * and the container as needed.
     *
     * @param FixtureInterface $fixture
     */
    protected function loadFixture(FixtureInterface $fixture)
    {
        if ($fixture instanceof AbstractFixture) {
            $fixture->setReferenceRepository($this->referenceRepo);
        }

        if ($fixture instanceof ContainerAwareInterface) {
            $fixture->setContainer($this->client->getContainer());
        }

        $fixture->load($this->em);
    }

    /**
     * Returns an object previously stored in the fixture reference repository.
     *
     * @param string $name Label of the fixture object to retrieve.
     * @return object
     */
    protected function getFixtureReference($name)
    {
        return $this->referenceRepo->getReference($name);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Magic method implementation used to easily load a fixture class or retrieve
     * a fixture reference.
     *
     * If there is a call to a method beginning by 'load' or 'get', this method will
     * try to make the appropriate call to #loadFixture or #getFixtureReference.
     *
     * Examples :
     * - $this->loadFooData('bar') is equivalent to $this->loadFixture(new LoadFooData('bar'))
     * - $this->getFoo('bar')      is equivalent to $this->getFixtureReference('foo/bar')
     *
     * Note : dynamic loading of fixture classes only works for fixture located in the core bundle.
     *
     * @param string    $name       Name of the method being called
     * @param array     $arguments  Arguments of the method being called
     * @return null|object
     * @throws Exception if a valid call to loadFixture or getFixtureReference cannot be made
     */
    public function __call($name, $arguments)
    {
        if (($isGet = strpos($name, 'load') !== 0) && strpos($name, 'get') !== 0) {
            throw new \Exception(
                "Cannot call {$name} : method is undefined and doesn't start "
                . "with the 'load' or 'get' fixture prefix"
            );
        }

        if ($isGet) {
            if (count($arguments) === 0) {
                throw new \Exception(
                    "Cannot call {$name} : method is undefined and one argument is "
                    . 'expected to get a fixture reference dynamically'
                );
            }

            $getParts = explode('get', $name);
            $target = strtolower($getParts[1]);

            return $this->getFixtureReference("{$target}/{$arguments[0]}");
        }

        $fixtureClass = 'Claroline\CoreBundle\Tests\DataFixtures\\' . ucfirst($name);

        if (!class_exists($fixtureClass)) {
            throw new \Exception("Cannot call {$name} : fixture class {$fixtureClass} doesn't exist");
        }

        $rFixture = new \ReflectionClass($fixtureClass);
        $this->loadFixture($rFixture->newInstanceArgs($arguments));
    }

    ///////////////// TO BE REMOVED /////////////////////

    protected function loadPlatformRolesFixture()
    {
        $this->loadFixture(new LoadPlatformRolesData());
    }

    protected function loadWorkspaceFixture($workspaces = null)
    {
        $this->loadFixture(new LoadWorkspaceData($workspaces));
    }

    protected function loadUserFixture(array $users = null)
    {
        $this->loadFixture(new LoadPlatformRolesData());
        $this->loadFixture(new LoadUserData($users));
    }

    protected function loadGroupFixture($groups = null)
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData($groups));
    }

    protected function loadRoleFixture()
    {
        $this->loadFixture(new LoadRoleData());
    }
}
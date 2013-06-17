<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class RepositoryTestCase extends WebTestCase
{
    protected static $client;
    protected static $referenceRepo;
    protected static $em;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$em = self::$client->getContainer()->get('doctrine.orm.entity_manager');
        self::$referenceRepo = new ReferenceRepository(self::$em);
        self::$client->beginTransaction();
    }

    public static function tearDownAfterClass()
    {
        self::$client->shutdown();
    }

    /**
     * Loads a fixture, injecting the entity manager, the reference repository
     * and the container as needed.
     *
     * @param FixtureInterface $fixture
     */
    protected static function loadFixture(FixtureInterface $fixture)
    {
        if ($fixture instanceof AbstractFixture) {
            $fixture->setReferenceRepository(self::$referenceRepo);
        }

        if ($fixture instanceof ContainerAwareInterface) {
            $fixture->setContainer(self::$client->getContainer());
        }

        $fixture->load(self::$em);
    }

    /**
     * Returns an object previously stored in the fixture reference repository.
     *
     * @param string $name Label of the fixture object to retrieve.
     * @return object
     */
    protected static function getFixtureReference($name)
    {
        return self::$referenceRepo->getReference($name);
    }

    public static function __callStatic($name, $arguments)
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

            return self::getFixtureReference("{$target}/{$arguments[0]}");
        }

        $fixtureClass = 'Claroline\CoreBundle\Tests\DataFixtures\\' . ucfirst($name);

        if (!class_exists($fixtureClass)) {
            throw new \Exception("Cannot call {$name} : fixture class {$fixtureClass} doesn't exist");
        }

        $rFixture = new \ReflectionClass($fixtureClass);
        self::loadFixture($rFixture->newInstanceArgs($arguments));
    }
}
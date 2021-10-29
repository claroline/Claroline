<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Fixtures;

use Claroline\AppBundle\Log\LoggableTrait;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Class responsible for loading the data fixtures of a bundle.
 */
class FixtureLoader implements LoggerAwareInterface
{
    use LoggableTrait;

    private $container;
    private $executor;

    /**
     * FixtureLoader constructor.
     */
    public function __construct(ContainerInterface $container, ORMExecutor $executor)
    {
        $this->container = $container;
        $this->executor = $executor;
    }

    /**
     * Loads the fixtures of a bundle. Fixtures are expected to be found in a
     * "DataFixtures/ORM" or "DataFixtures" directory within the bundle. Note
     * that fixtures are always appended (no purge/truncation).
     *
     * @param string $fixturesDirectory
     *
     * @return bool True if some fixtures have been found and executed, false otherwise
     */
    public function load(BundleInterface $bundle, $fixturesDirectory = 'DataFixtures')
    {
        // we must get a fresh instance of the loader (scope = prototype)
        // to avoid re-executing previously loaded fixtures
        $loader = $this->container->get('claroline.symfony_fixture_loader');
        $directory = "{$bundle->getPath()}/{$fixturesDirectory}";
        $loader->loadFromDirectory($directory);
        $fixtures = $loader->getFixtures();

        foreach ($fixtures as $fixture) {
            $this->log(sprintf('Found %s fixture to load', get_class($fixture)));

            if (method_exists($fixture, 'setLogger')) {
                $fixture->setLogger($this->logger);
            }
        }

        if ($fixtures) {
            $this->executor->execute($fixtures, true);

            return true;
        }

        return false;
    }
}

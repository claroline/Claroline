<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class responsible for loading the data fixtures of a bundle.
 *
 * @DI\Service("claroline.installation.fixture_loader")
 */
class FixtureLoader
{
    private $loader;
    private $executor;

    /**
     * @DI\InjectParams({
     *     "loader"     = @DI\Inject("claroline.symfony_fixture_loader"),
     *     "executor"   = @DI\Inject("claroline.doctrine_fixture_executor")
     * })
     */
    public function __construct(ContainerAwareLoader $loader, ORMExecutor $executor)
    {
        $this->loader = $loader;
        $this->executor = $executor;
    }

    /**
     * Loads the fixtures of a bundle. Fixtures are expected to be found in a
     * "DataFixtures/ORM" or "DataFixtures" directory within the bundle. Note
     * that fixtures are always appended (no purge/truncation).
     *
     * @param \Symfony\Component\HttpKernel\Bundle\Bundle $bundle
     *
     * @return boolean True if some fixtures have been found and executed, false otherwise
     */
    public function load(Bundle $bundle)
    {
        $baseDir = "{$bundle->getPath()}/DataFixtures";
        $ormDir = "{$baseDir}/ORM";

        if (is_dir($ormDir)) {
            $this->loader->loadFromDirectory($ormDir);
        } elseif (is_dir($baseDir)) {
            $this->loader->loadFromDirectory($baseDir);
        } else {
            return false;
        }

        $fixtures = $this->loader->getFixtures();

        if ($fixtures) {
            $this->executor->execute($fixtures, true);

            return true;
        }

        return false;
    }
}

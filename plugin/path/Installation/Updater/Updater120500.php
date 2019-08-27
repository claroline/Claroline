<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120500 extends Updater
{
    /** @var ContainerInterface */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->updateSlugs();
    }

    /**
     * Initializes progression of path evaluation.
     */
    private function updateSlugs()
    {
        $this->log('Generating slugs for steps without slugs...');
        $conn = $this->container->get('doctrine.dbal.default_connection');
        $sql = "
             UPDATE innova_step step SET slug = CONCAT(SUBSTR(step.title,1,100) , '-', step.id) WHERE step.slug = NULL
        ";

        $this->log($sql);
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
}

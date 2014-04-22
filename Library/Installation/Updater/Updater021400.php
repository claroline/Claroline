<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

class Updater021400
{
    private $container;
    private $logger;
    private $oldCachePath;
    private $newCachePath;

    public function __construct($container)
    {
        $this->container = $container;
        $ds = DIRECTORY_SEPARATOR;
        $this->oldCachePath = $container
            ->getParameter('kernel.root_dir') . $ds . 'cache' . $ds . 'claroline.cache.php';
        $this->newCachePath = $container
                ->getParameter('kernel.root_dir') . $ds . 'cache' . $ds . 'claroline.cache.ini';
    }

    public function postUpdate()
    {
        $this->log('Updating cache...');
        $this->container->get('claroline.manager.cache_manager')->refresh();
        $this->log('Removing old cache...');

        if (file_exists($this->oldCachePath)) {
            unlink($this->oldCachePath);
        }

    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}
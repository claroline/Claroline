<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\StrictDispatcher;

/**
 * @DI\Service("claroline.manager.cache_manager")
 */
class CacheManager {

    private $eventManager;

    /**
     * @DI\InjectParams({
     *      "rootDir" = @DI\Inject("%kernel.root_dir%"),
     *      "eventManager" = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(StrictDispatcher $eventManager, $rootDir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->cachePath = $rootDir . $ds . 'cache' . $ds . 'claroline.cache.ini';
        $this->eventManager = $eventManager;
    }

    /**
     * Read a value from the claroline cache.
     *
     * @param $parameter
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getParameter($parameter)
    {
        $isRefreshed = false;

        if (!$this->cacheExists()) {
            $this->refresh();
            $isRefreshed = true;
        }

        $values = parse_ini_file($this->cachePath);
        $return = null;

        if (isset($values[$parameter])) {
            $return = $values[$parameter];
        } else {
            if (!$isRefreshed) {
                $this->refresh();
                $values = parse_ini_file($this->cachePath);

                if (isset($values[$parameter])) {
                    $return = $values[$parameter];
                }
            }
        }

        return $return ? $return: false;
    }

    /**
     * Refresh the claroline cache
     */
    public function refresh()
    {
        $this->removeCache();
        $event = $this->eventManager->dispatch('refresh_cache', 'RefreshCache');
        $this->writeIniFile($event->getParameters(), $this->cachePath);
    }

    /**
     * Remove the claroline cache.
     */
    private function removeCache()
    {
        if ($this->cacheExists()) {
            unlink($this->cachePath);
        }
    }

    /**
     * @return bool
     */
    private function cacheExists()
    {
        return file_exists($this->cachePath);
    }

    /**
     * @param array $parameters
     * @param string $iniFile
     *
     * @throws \Exception
     */
    private function writeIniFile(array $parameters, $iniFile)
    {
        $content = '';

        foreach ($parameters as $key => $value) {
            $content .= "{$key} = {$value}\n";
        }

        if (!file_put_contents($iniFile, $content)) {
            throw new \Exception("The claroline cache couldn't be created");
        }

    }
} 
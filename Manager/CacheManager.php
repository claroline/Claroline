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
        $this->cachePath = $rootDir . $ds . 'cache' . $ds . 'claroline.cache.php';
        $this->eventManager = $eventManager;
    }

    /**
     * Read a value from the claroline cache.
     *
     * @param $parameter
     *
     * @return
     */
    public function getParameter($parameter)
    {
        $isRefreshed = false;

        if (!$this->cacheExists()) {
            $this->refresh();
            $isRefreshed = true;
        }

        $values = include $this->cachePath;

        if (isset($values[$parameter])) {
            return $values[$parameter];
        } else {
            if (!$isRefreshed) {
                $this->refresh();
                $values = include $this->cachePath;

                if (isset($values[$parameter])) {
                    return $values[$parameter];
                }
            }
        }

        return null;
    }

    /**
     * Refresh the claroline cache
     */
    public function refresh()
    {
        $this->removeCache();
        $event = $this->eventManager->dispatch('refresh_cache', 'RefreshCache');
        $value = var_export($event->getParameters(), true);
        $code = sprintf('<?php return %s;', $value);
        if (!file_put_contents($this->cachePath, $code)) {
            throw new \Exception("The claroline cache couldn't be created");
        }
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
} 
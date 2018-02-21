<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Event\StrictDispatcher;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.cache_manager")
 */
class CacheManager
{
    private $eventManager;
    private $cachePath;
    private $iniFileManager;

    /**
     * @DI\InjectParams({
     *      "rootDir"        = @DI\Inject("%kernel.root_dir%"),
     *      "eventManager"   = @DI\Inject("claroline.event.event_dispatcher"),
     *      "iniFileManager" = @DI\Inject("claroline.manager.ini_file_manager")
     * })
     */
    public function __construct(StrictDispatcher $eventManager, $rootDir, IniFileManager $iniFileManager)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->cachePath = $rootDir.$ds.'cache'.$ds.'claroline.cache.ini';
        $this->eventManager = $eventManager;
        $this->iniFileManager = $iniFileManager;
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

        return $return ? $return : false;
    }

    public function getParameters()
    {
        return $this->cacheExists() ? parse_ini_file($this->cachePath) : [];
    }

    public function setParameter($parameter, $value)
    {
        $values = $this->getParameters();
        $values[$parameter] = $value;
        $this->writeCache($values);
    }

    /**
     * Refresh the claroline cache.
     */
    public function refresh()
    {
        $this->removeCache();
        $event = $this->eventManager->dispatch('refresh_cache', 'RefreshCache');
        $this->writeCache($event->getParameters());
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
     */
    public function writeCache(array $parameters)
    {
        $this->iniFileManager->writeIniFile($parameters, $this->cachePath);
    }
}

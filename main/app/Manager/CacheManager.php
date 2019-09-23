<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Manager;

use Claroline\AppBundle\Event\App\RefreshCacheEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Parser\IniParser;

/**
 * Manages the application cache.
 */
class CacheManager
{
    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var string */
    private $cachePath;

    /**
     * CacheManager constructor.
     *
     * @param StrictDispatcher $eventDispatcher
     * @param string           $rootDir
     */
    public function __construct(StrictDispatcher $eventDispatcher, $rootDir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->cachePath = $rootDir.$ds.'cache'.$ds.'claroline.cache.ini';
        $this->eventDispatcher = $eventDispatcher;
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
        return IniParser::parseFile($this->cachePath);
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

        /** @var RefreshCacheEvent $event */
        $event = $this->eventDispatcher->dispatch('refresh_cache', RefreshCacheEvent::class);

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
        IniParser::dumpFile($parameters, $this->cachePath);
    }
}

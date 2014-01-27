<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.cache_manager")
 */
class CacheManager {

    /**
     * @DI\InjectParams({
            "rootPath" = @DI\Inject("%kernel.root_dir%")
     * })
     */
    public function __construct($rootPath)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->cachePath = "{$rootPath}{$ds}cache{$ds}claroline.cache.php";
    }

    public function cacheExists()
    {
        return file_exists($this->cachePath);
    }

    public function getParameter($parameter)
    {
        if (!$this->cacheExists()) {
            return null;
        }
    }

    public function save()
    {

    }

    public function removeCache()
    {
        if ($this->cacheExists()) {
            //remove cache
        }
    }

    public function createCache()
    {
        $data = <<<EOT
        <?php

        class ClaroCache {
            public static parameters = array();
        }
EOT;

        file_put_contents($this->cachePath, $data);
    }
} 
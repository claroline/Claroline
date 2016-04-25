<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Yaml\Yaml;

/**
 * @DI\Service("claroline.importer.merger")
 * @todo: testme
 */
class Resolver
{
    public function __construct($path, $rootFile = 'manifest.yml')
    {
        $this->path = $path;
        $this->rootFile = $rootFile;
    }

    public function resolve()
    {
        $ds = DIRECTORY_SEPARATOR;
        $data = Yaml::parse(file_get_contents($this->path.$ds.$this->rootFile));
        $this->parse($data);
        //parse all the include path and inject them into a single array

        return $data;
    }

    private function parse(&$data)
    {
        $ds = DIRECTORY_SEPARATOR;

        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if ($key === 'import') {
                    foreach ($value as $path) {
                        $inject = Yaml::parse(file_get_contents($this->path.$ds.$path['path']));

                        foreach ($inject as $el) {
                            foreach ($el as $item) {
                                $data[array_keys($inject)[0]][] = $item;
                                $this->parse($data[array_keys($inject)[0]]);
                            }
                        }
                    }
                }

                $this->parse($value);
            }
        }

        return $data;
    }
}

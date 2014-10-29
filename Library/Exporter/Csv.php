<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Exporter;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.exporter.csv")
 */
class Csv implements ExporterInterface
{
    public function export(array $titles, array $data)
    {
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "file.csv";
        $fp = fopen($tmpFile, 'w');

        fputcsv($fp, $titles);

        foreach($data as $item) {
            fputcsv($fp, $item);
        }

        fclose($fp);

        return $tmpFile;
    }
}

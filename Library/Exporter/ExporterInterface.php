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

/**
 * @deprecated
 * This is now supported by the view layer of the fos_rest bundle
 */
interface ExporterInterface
{
    public function export(array $titles, array $data);
}

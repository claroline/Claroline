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

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\DataSourceInterface;
use Claroline\AppBundle\Component\DataSource\DataSourceProvider;

class DataSourceManager
{
    public function __construct(
        private readonly DataSourceProvider $dataSourceProvider
    ) {
    }

    /**
     * Get the list of available sources in the platform.
     */
    public function getAvailable(string $context, ?ContextSubjectInterface $contextSubject = null): array
    {
        $available = $this->dataSourceProvider->getAvailableSources($context, $contextSubject);

        return array_map(function (DataSourceInterface $dataSource) {
            return [
                'name' => $dataSource::getName(),
                'type' => $dataSource::getType(),
            ];
        }, $available);
    }
}

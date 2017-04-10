<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Transfert;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @DI\Service("claroline.tool.resources.scorm12_importer")
 * @DI\Tag("claroline.importer")
 */
class Scorm12Importer extends ScormImporter implements ConfigurationInterface
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        parent::__construct($container);
    }

    public function getName()
    {
        return 'claroline_scorm_12';
    }
}

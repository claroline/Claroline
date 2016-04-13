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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.tool.resources.scorm2004_importer")
 * @DI\Tag("claroline.importer")
 */
class Scorm2004Importer extends ScormImporter implements ConfigurationInterface
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
        return 'claroline_scorm_2004';
    }
}

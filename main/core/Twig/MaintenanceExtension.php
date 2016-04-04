<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class MaintenanceExtension extends \Twig_Extension
{
    private $container;

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'is_maintenance_enabled' => new \Twig_Function_Method($this, 'isMaintenanceEnabled'),
        );
    }

    public function isMaintenanceEnabled()
    {
        return MaintenanceHandler::isMaintenanceEnabled();
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'is_maintenance_enabled_extension';
    }
}

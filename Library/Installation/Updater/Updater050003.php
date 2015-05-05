<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater050003 extends Updater
{
    private $container;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Updating defualt workspacetemplate directory...');
        $destinationPath = $this->container->getParameter('claroline.param.templates_directory'). '/default.zip';
        $sourcePath = $this->container->getParameter('claroline.param.default_template');
        @unlink($destinationPath);
        copy($sourcePath, $destinationPath);
    }
}

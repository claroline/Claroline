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

class Updater060000 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->updateTemplate();
    }

    private function updateTemplate()
    {
        $this->log('Updating template file');
        $destinationPath = $this->container->getParameter('claroline.param.templates_directory').'/personal.zip';
        @unlink($destinationPath);
        copy($this->container->getParameter('claroline.param.personal_template'), $destinationPath);
    }
}

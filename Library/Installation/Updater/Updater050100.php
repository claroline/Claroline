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
use Symfony\Component\Filesystem\Filesystem;

class Updater050100 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Moving default template...');

        $fileDir = $this->container->getParameter('claroline.param.files_directory');
        $defaultTemplate = $this->container->getParameter('claroline.param.default_template');
        $newTemplateDir = $fileDir . '/templates';
        $newTemplate = $newTemplateDir . '/default.zip';

        $fs = new Filesystem();
        $fs->mkdir($newTemplateDir);
        $fs->copy($defaultTemplate, $newTemplate);
    }
}

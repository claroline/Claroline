<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/1/17
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class Updater110000 extends Updater
{
    private $container;
    protected $logger;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    public function postUpdate()
    {
        //old compatibility for pictures
        $this->lnPictureDirectory();
        $this->lnPackageDirectory();
    }

    public function lnPictureDirectory()
    {
        $fileSystem = $this->container->get('filesystem');
        $webDir = $this->container->getParameter('claroline.param.web_directory');

        if (!$fileSystem->exists($webDir.'/uploads/pictures/data')) {
            $this->log('Creating symlink to '.$webDir.'/uploads/pictures/data');
            $fileSystem->symlink($webDir.'/data', $webDir.'/uploads/pictures/data');
        }
    }

    public function lnPackageDirectory()
    {
        $fileSystem = $this->container->get('filesystem');
        $webDir = $this->container->getParameter('claroline.param.web_directory');

        if (!$fileSystem->exists($webDir.'/packages')) {
            $this->log('Creating symlink to '.$webDir.'/packages');
            $fileSystem->symlink($webDir.'/../node_modules', $webDir.'/packages');
        } else {
            if (!is_link($webDir.'/packages')) {
                $this->log('Couldn\'t create symlink to from node_modules to web/packages. You must remove web/packages or create the link manually');
            }
        }
    }
}

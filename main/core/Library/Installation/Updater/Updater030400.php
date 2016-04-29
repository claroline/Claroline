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

class Updater030400 extends Updater
{
    private $em;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('replacing default zip file');
        $this->replaceDefaultZip();
    }

    private function replaceDefaultZip()
    {
        $destinationPath = $this->container->getParameter('claroline.param.templates_directory').'/default.zip';
        $sourcePath = $this->container->getParameter('claroline.param.default_template');
        @unlink($destinationPath);
        copy($sourcePath, $destinationPath);
    }
}

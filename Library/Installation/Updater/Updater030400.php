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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Widget\Widget;

class Updater030400 {
    private $em;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->replaceDefaultZip();
    }

    private function replaceDefaultZip()
    {
        $destinationPath = $this->container->getParameter('claroline.param.templates_directory'). '/default.zip';
        $sourcePath = $this->container->getParameter('claroline.param.default_template');
        @unlink($destinationPath);
        copy($sourcePath, $destinationPath);
    }
} 
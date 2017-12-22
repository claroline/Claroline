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

class Updater100030 extends Updater
{
    private $container;
    protected $logger;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->maskManager = $this->container->get('claroline.manager.mask_manager');
        $this->roleManager = $this->container->get('claroline.manager.role_manager');
    }

    public function postUpdate()
    {
        $this->maskManager->setLogger($this->logger);
        $this->roleManager->setLogger($this->logger);
        $this->maskManager->checkIntegrity();
        $this->roleManager->checkIntegrity();
    }
}

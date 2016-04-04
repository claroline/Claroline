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

class Updater041000 extends Updater
{
    private $container;
    private $om;
    private $toolManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->toolManager = $container->get('claroline.manager.tool_manager');
    }

    public function postUpdate()
    {
        $this->changeParametersToolIcon();
    }

    private function changeParametersToolIcon()
    {
        $this->log('Changing parameters tool icon...');
        $tool = $this->toolManager->getOneToolByName('parameters');

        if (!is_null($tool)) {
            $tool->setClass('cogs');
            $this->om->persist($tool);
            $this->om->flush();
        }
    }
}

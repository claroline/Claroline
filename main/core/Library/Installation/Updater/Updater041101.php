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

class Updater041101 extends Updater
{
    private $container;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->deleteCompetencyTool();
    }

    private function deleteCompetencyTool()
    {
        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')
            ->findOneByName('competence_referencial');

        if ($tool) {
            $this->log('Removing previous competency tool...');
            $this->om->remove($tool);
            $this->om->flush();
        }
    }
}

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

class Updater041200 extends Updater
{
    private $container;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->removeAdminDesktopTool();
        $this->translateOrderedTools();
        $this->translateTools();
    }

    private function removeAdminDesktopTool()
    {
        $this->log('Removing desktop admin tool...');
        $repo = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool');
        $toRemove = $repo->findOneByName('desktop_tools');
        if ($toRemove) $this->om->remove($toRemove);
    }

    public function translateOrderedTools()
    {

        $repo = $this->om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $qb = $repo->createQueryBuilder("orderedTool");
        $i = 0;
        $rows = $qb->getQuery()->iterate();
        $this->log('Updating ordered tools translations...');
        $countqb = clone $qb;
        $totalTools = $countqb->select('COUNT(orderedTool.id)')
            ->getQuery()->getSingleScalarResult();

        $this->log(sprintf('Updating %d tool translations - %s', $totalTools, date('Y/m/d H:i:s')));
        $this->log('It may take a while to process, go grab a coffee.');

        foreach ($rows as $row) {
            $ot = $row[0];
            $this->container->get('claroline.manager.tool_manager')->setDefaultOrderedToolTranslations($ot);

            if ($i % 200 === 0) {
                $this->log(sprintf("    %d tools updated - %s...", $i, date('Y/m/d H:i:s')));
                $this->om->flush();
                $this->om->clear();
            }

            $i++;
        }
        $this->om->flush();
    }

    public function translateTools()
    {
        $tools = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findAll();
        $this->log('Updating tools translations');

        foreach ($tools as $tool) {
            $this->container->get('claroline.manager.tool_manager')->setDefaultToolTranslations($tool);
        }
        $this->om->flush();
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.manager.home_tab_manager")
 */
class HomeTabManager
{
    use LoggableTrait;
    /** @var HomeTabConfigRepository */
    private $homeTabConfigRepo;
    /** @var HomeTabRepository */
    private $homeTabRepo;
    private $om;
    private $container;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" =  @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container, ObjectManager $om)
    {
        $this->homeTabRepo = $om->getRepository('ClarolineCoreBundle:Tab\HomeTab');
        $this->homeTabConfigRepo = $om->getRepository('ClarolineCoreBundle:Tab\HomeTabConfig');
        $this->container = $container;
        $this->om = $om;
    }

    //HomeTabByCsc Command
    public function importFromCsv($file)
    {
        $data = file_get_contents($file);
        $data = $this->container->get('claroline.utilities.misc')->formatCsvOutput($data);
        $lines = str_getcsv($data, PHP_EOL);
        $this->om->startFlushSuite();
        $i = 0;

        foreach ($lines as $line) {
            $values = str_getcsv($line, ';');
            $code = $values[0];
            $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);

            $name = $values[1];
            $tab = $this->om->getRepository('ClarolineCoreBundle:Tab\HomeTab')->findBy(['workspace' => $workspace, 'name' => $name]);
            if (!$tab) {
                $this->createHomeTab($name, $workspace);
                ++$i;
            } else {
                $this->log("Tab {$name} already exists for workspace {$code}");
            }

            if (0 === $i % 100) {
                $this->om->forceFlush();
                $this->om->clear();
            }
        }

        $this->om->endFlushSuite();
    }

    //at least used by HomeTabByCsc Command
    public function createHomeTab($name, Workspace $workspace = null)
    {
        $type = $workspace ? 'workspace' : 'user';
        $homeTab = new HomeTab();

        $homeTab->setWorkspace($workspace);
        $homeTab->setType($type);

        $homeTabConfig = new HomeTabConfig();
        $homeTabConfig->setHomeTab($homeTab);
        $homeTabConfig->setName($name);
        $homeTabConfig->setLongTitle($name);

        $tabsInserted = $this->homeTabRepo->findByWorkspace($workspace);
        $tabsToInsert = $this->getTabsScheduledForInsert($workspace);
        $index = count($tabsInserted) + count($tabsToInsert);
        $homeTabConfig->setTabOrder($index);

        $this->om->persist($homeTabConfig);
        $this->om->persist($homeTab);
        $this->om->flush();

        $this->log("Creating HomeTab {$name} for workspace {$workspace->getCode()}.");
    }

    //at least used by HomeTabByCsc Command
    public function getTabsScheduledForInsert(Workspace $workspace)
    {
        $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();
        $res = [];

        foreach ($scheduledForInsert as $entity) {
            if ('Claroline\CoreBundle\Entity\Tab\HomeTab' === get_class($entity)) {
                if ($entity->getWorkspace()->getCode() === $workspace->getCode()) {
                    $res[] = $entity;
                }
            }
        }

        return $res;
    }

    /**
     * That one is used twice.
     */
    public function getHomeTabByWorkspace(Workspace $workspace)
    {
        return $this->homeTabRepo->findBy(['workspace' => $workspace]);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}

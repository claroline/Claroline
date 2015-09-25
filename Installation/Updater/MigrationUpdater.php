<?php

namespace Icap\BadgeBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrationUpdater extends Updater
{
    private $container;
    private $conn;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Claroline\CoreBundle\Entity\Plugin
     */
    private $badgePlugin;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->conn = $container->get('database_connection');
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    public function preInstall()
    {
        $this->skipInstallIfMigratingFromCore();
    }

    public function postInstall()
    {
        /** @var \Claroline\CoreBundle\Repository\PluginRepository $pluginRepository */
        $pluginRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Plugin');

        $this->badgePlugin = $pluginRepository->createQueryBuilder('plugin')
            ->where('plugin.vendorName = :badgeVendorName')
            ->andWhere('plugin.bundleName = :badgeShortName')
            ->setParameters(['badgeVendorName' => 'Icap', 'badgeShortName' => 'BadgeBundle'])
            ->getQuery()
            ->getSingleResult();

        $this->entityManager->flush();

        $this->reusePreviousAdminToolsIfAny();
        $this->reusePreviousToolsIfAny();
        $this->reusePreviousWidgetsIfAny();
        $this->insertWidgetTypeDataForPortfolio();
    }

    private function skipInstallIfMigratingFromCore()
    {
        if ($this->conn->getSchemaManager()->tablesExist(['claro_badge'])) {
            $this->log('Found existing database schema: skipping install migration...');
            $config = new Configuration($this->conn);
            $config->setMigrationsTableName('doctrine_icapbadgebundle_versions');
            $config->setMigrationsNamespace('claro_badge'); // required but useless
            $config->setMigrationsDirectory('claro_badge'); // idem
            $version = new Version($config, '20150506091116', 'stdClass');
            $version->markMigrated();
        }
    }

    private function reusePreviousAdminToolsIfAny()
    {
        $listAdminTools = ['badges_management'];

        foreach ($listAdminTools as $listAdminTool) {
            $this->reusePreviousExtensionIfAny('admintool', $listAdminTool);
        }
    }

    private function reusePreviousToolsIfAny()
    {
        $listTools = ['badges', 'my_badges', 'all_my_badges'];

        foreach ($listTools as $listTool) {
            $this->reusePreviousExtensionIfAny('tool', $listTool);
        }
    }

    private function reusePreviousWidgetsIfAny()
    {
        $listWidgets = ['badge_usage'];

        foreach ($listWidgets as $listWidget) {
            $this->reusePreviousExtensionIfAny('widget', $listWidget);
        }
    }

    private function reusePreviousExtensionIfAny($type, $name)
    {
        if ($previous = $this->findWithNoPlugin($type, $name)) {
            $this->log("Re-using previous {$name} {$type}...");
            $current = $this->find($type, $name);
            $current->setName($name . '_tmp');
            $this->entityManager->persist($current);
            $this->entityManager->flush();
            $previous->setPlugin($current->getPlugin());
            $this->entityManager->persist($previous);
            $this->entityManager->flush();
            $this->delete($type, $name . '_tmp');
        }
    }

    private function find($type, $name)
    {
        return $this->entityManager
            ->getRepository($this->getClassFromType($type))
            ->findOneBy(['name' => $name, 'plugin' => $this->badgePlugin]);
    }

    private function findWithNoPlugin($type, $name)
    {
        return $this->entityManager
            ->getRepository($this->getClassFromType($type))
            ->findOneBy(['name' => $name, 'plugin' => null]);
    }

    private function delete($type, $name)
    {
        $this->entityManager->createQueryBuilder()
            ->delete()
            ->from($this->getClassFromType($type), 't')
            ->where('t.name = :name')
            ->getQuery()
            ->setParameter(':name', $name)
            ->execute();
    }

    private function getClassFromType($type)
    {
        $class = '';

        switch($type) {
            case 'admintool':
                $class = 'Claroline\CoreBundle\Entity\Tool\AdminTool';
                break;
            case 'tool':
                $class = 'Claroline\CoreBundle\Entity\Tool\Tool';
                break;
            case 'widget':
                $class= 'Claroline\CoreBundle\Entity\Widget\Widget';
                break;
        }

        return $class;
    }

    private function insertWidgetTypeDataForPortfolio()
    {
        /** @var \Claroline\CoreBundle\Repository\PluginRepository $pluginRepository */
        $pluginRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Plugin');

        $portfolioPlugin = $pluginRepository->createQueryBuilder('plugin')
            ->where('plugin.vendorName = :portfolioVendorName')
            ->andWhere('plugin.bundleName = :portfolioShortName')
            ->setParameters(['portfolioVendorName' => 'Icap', 'portfolioShortName' => 'PortfolioBundle'])
            ->getQuery()
            ->getOneOrNullResult();

        if (null !== $portfolioPlugin) {
            /** @var \Icap\PortfolioBundle\Repository\Widget\WidgetTypeRepository $widgetTypeRepository */
            $widgetTypeRepository = $this->entityManager->getRepository('IcapPortfolioBundle:Widget\WidgetType');

            $badgeWidgetType = $widgetTypeRepository->createQueryBuilder('widgetType')
                ->where('widgetType.name = :badgetWidgetTypeName')
                ->setParameter('badgetWidgetTypeName', 'badges')
                ->getQuery()
                ->getOneOrNullResult();

            if (null === $badgeWidgetType) {
                $widgetType = new \Icap\PortfolioBundle\Entity\Widget\WidgetType();
                $widgetType
                    ->setName('badges')
                    ->setIcon('trophy');

                $this->entityManager->persist($widgetType);
                $this->log("Badge widget type created for portfolio.");
            }
        }

        $this->entityManager->flush();
    }
}

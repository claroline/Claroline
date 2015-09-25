<?php

namespace Icap\PortfolioBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
use Icap\PortfolioBundle\Entity\Widget\WidgetType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrationUpdater extends Updater
{
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
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    public function postInstall()
    {
        /** @var \Claroline\CoreBundle\Repository\PluginRepository $pluginRepository */
        $pluginRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Plugin');

        $badgePlugin = $pluginRepository->createQueryBuilder('plugin')
            ->where('plugin.vendorName = :badgeVendorName')
            ->andWhere('plugin.bundleName = :badgeShortName')
            ->setParameters(['badgeVendorName' => 'Icap', 'badgeShortName' => 'BadgeBundle'])
            ->getQuery()
            ->getOneOrNullResult();

        if (null !== $badgePlugin) {
            /** @var \Icap\PortfolioBundle\Repository\Widget\WidgetTypeRepository $widgetTypeRepository */
            $widgetTypeRepository = $this->entityManager->getRepository('IcapPortfolioBundle:Widget\WidgetType');

            $badgeWidgetType = $widgetTypeRepository->createQueryBuilder('widgetType')
                ->where('widgetType.name = :badgetWidgetTypeName')
                ->setParameter('badgetWidgetTypeName', 'badges')
                ->getQuery()
                ->getOneOrNullResult();

            if (null === $badgeWidgetType) {
                $widgetType = new WidgetType();
                $widgetType
                    ->setName('badges')
                    ->setIcon('trophy');

                $this->entityManager->persist($widgetType);
                $this->log("Badge widget type created.");
            }
        }

        $this->entityManager->flush();
    }
}

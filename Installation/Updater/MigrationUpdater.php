<?php

namespace Icap\PortfolioBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
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

        $portfolioPlugin = $pluginRepository->createQueryBuilder('plugin')
            ->where('plugin.vendorName = :badgeVendorName')
            ->andWhere('plugin.bundleName = :badgeShortName')
            ->setParameters(['badgeVendorName' => 'Icap', 'badgeShortName' => 'BadgeBundle'])
            ->getQuery()
            ->getOneOrNullResult();

        if (null !== $portfolioPlugin) {
            $widgetType = new \Icap\PortfolioBundle\Entity\Widget\WidgetType();
            $widgetType
                ->setName('badges')
                ->setIcon('trophy');

            $this->entityManager->persist($widgetType);
            $this->log("Badge widget type created.");
        }

        $this->entityManager->flush();
    }
}

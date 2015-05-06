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
    private $em;

    /**
     * @var \Claroline\CoreBundle\Entity\Plugin
     */
    private $badgePlugin;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->conn = $container->get('database_connection');
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function preInstall()
    {
        $this->skipInstallIfMigratingFromCore();
    }

    public function postInstall()
    {
        /** @var \Claroline\CoreBundle\Repository\PluginRepository $pluginRepository */
        $pluginRepository = $this->em->getRepository('ClarolineCoreBundle:Plugin');

        $this->badgePlugin = $pluginRepository->createQueryBuilder('plugin')
            ->where('plugin.vendorName = :badgeVendorName')
            ->andWhere('plugin.bundleName = :badgeShortName')
            ->setParameters(['badgeVendorName' => 'Icap', 'badgeShortName' => 'BadgeBundle'])
            ->getQuery()
            ->getSingleResult();

        $this->em->flush();

        $this->reusePreviousAdminToolsIfAny();
        $this->reusePreviousToolsIfAny();
        $this->reusePreviousWidgetsIfAny();
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
            $this->em->persist($current);
            $this->em->flush();
            $previous->setPlugin($current->getPlugin());
            $this->em->persist($previous);
            $this->em->flush();
            $this->delete($type, $name . '_tmp');
        }
    }

    private function find($type, $name)
    {
        return $this->em
            ->getRepository($this->getClassFromType($type))
            ->findOneBy(['name' => $name, 'plugin' => $this->badgePlugin]);
    }

    private function findWithNoPlugin($type, $name)
    {
        return $this->em
            ->getRepository($this->getClassFromType($type))
            ->findOneBy(['name' => $name, 'plugin' => null]);
    }

    private function delete($type, $name)
    {
        $this->em->createQueryBuilder()
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
}

<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 17/10/17
 * Time: 14:10.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Command\AdminCliCommand;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HomeTabCommand extends ContainerAwareCommand implements AdminCliCommand
{
    protected function configure()
    {
        $this->setName('claroline:home:build-default')
            ->setDescription('This command allow you to rebuild the default tabs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $manager = $container->get('claroline.persistence.object_manager');
        $translator = $container->get('translator');
        $finder = $container->get('claroline.api.finder');
        $tabs = $finder->fetch(HomeTab::class, ['type' => HomeTab::TYPE_ADMIN_DESKTOP]);

        if (0 === count($tabs)) {
            $output->writeln('No tabs found... restoring default.');
            $infoName = $translator->trans('informations', [], 'platform');

            $desktopHomeTab = new HomeTab();
            $desktopHomeTab->setType(HomeTab::TYPE_ADMIN_DESKTOP);
            $manager->persist($desktopHomeTab);

            $desktopHomeTabConfig = new HomeTabConfig();
            $desktopHomeTabConfig->setHomeTab($desktopHomeTab);
            $desktopHomeTabConfig->setVisible(true);
            $desktopHomeTabConfig->setLocked(false);
            $desktopHomeTabConfig->setTabOrder(1);
            $desktopHomeTabConfig->setName($infoName);
            $desktopHomeTabConfig->setLongTitle($infoName);

            $manager->persist($desktopHomeTabConfig);
            $manager->flush();
        }

        foreach ($tabs as $tab) {
            $homeTabConfig = $this->getConfig($tab);
            $restoreConfig = false;

            if (!$homeTabConfig) {
                $restoreConfig = true;
            }
        }

        if ($restoreConfig) {
            $output->writeln('Config not found... restoring...');

            foreach ($tabs as $i => $tab) {
                $homeTabConfig = $this->getConfig($tab);

                if (!$homeTabConfig) {
                    $homeTabConfig = new HomeTabConfig();
                    $homeTabConfig->setName('Restored');
                    $homeTabConfig->setHomeTab($tab);
                    $homeTabConfig->setLongTitle('Restored');
                }

                $homeTabConfig->setTabOrder($i);
                $manager->persist($homeTabConfig);
            }

            $manager->flush();
        }

        $workspaces = $container->get('claroline.persistence.object_manager')->getRepository(Workspace::class)->findAll();

        $output->writeln(count($workspaces).' found');
        $i = 1;
        $j = 1;

        //todo: le faire en sql pour aller plus vite
        foreach ($workspaces as $workspace) {
            $output->writeln('Workspace '.$i.' :');

            $tabs = $finder->fetch(HomeTab::class, ['workspace' => $workspace->getUuid()]);

            if (0 === count($tabs)) {
                $output->writeln('No tabs found... restoring default.');
                $infoName = $translator->trans('informations', [], 'platform');

                $workspaceTab = new HomeTab();
                $workspaceTab->setType(HomeTab::TYPE_WORKSPACE);
                $workspaceTab->setWorkspace($workspace);
                $manager->persist($workspaceTab);

                $workspaceTabConfig = new HomeTabConfig();
                $workspaceTabConfig->setHomeTab($workspaceTab);
                $workspaceTabConfig->setVisible(true);
                $workspaceTabConfig->setLocked(true);
                $workspaceTabConfig->setTabOrder(1);
                $workspaceTabConfig->setName($infoName);
                $workspaceTabConfig->setLongTitle($infoName);

                $manager->persist($workspaceTabConfig);
                ++$j;
            }

            foreach ($tabs as $tab) {
                $homeTabConfig = $this->getConfig($tab);
                $restoreConfig = false;

                if (!$homeTabConfig) {
                    $restoreConfig = true;
                }
            }

            if ($restoreConfig) {
                $output->writeln('Config not found... restoring...');

                foreach ($tabs as $i => $tab) {
                    $homeTabConfig = $this->getConfig($tab);

                    if (!$homeTabConfig) {
                        $homeTabConfig = new HomeTabConfig();
                        $homeTabConfig->setName('Restored');
                        $homeTabConfig->setHomeTab($tab);
                        $homeTabConfig->setLongTitle('Restored');
                    }

                    $homeTabConfig->setTabOrder($i);
                    $manager->persist($homeTabConfig);
                }

                $manager->flush();
            }

            if (0 === $j % 500) {
                $manager->flush();
                $j = 1;
            }
            ++$i;
        }

        $manager->flush();
    }

    /**
     * @param HomeTab $tab
     *
     * @return HomeTabConfig
     */
    public function getConfig(HomeTab $tab)
    {
        $container = $this->getContainer();
        $manager = $container->get('claroline.persistence.object_manager');

        /** @var HomeTabConfig $homeTabConfig */
        $homeTabConfig = $manager->getRepository(HomeTabConfig::class)
          ->findOneBy(['homeTab' => $tab]);

        return $homeTabConfig;
    }
}

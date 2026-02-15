<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Additional;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Update\UpdaterExecution;
use Claroline\InstallationBundle\Repository\UpdaterExecutionRepository;
use Claroline\InstallationBundle\Updater\NonReplayableUpdaterInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AdditionalInstaller implements AdditionalInstallerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ?ContainerInterface $container;

    /**
     * Whether updaters should be executed even if they have been already.
     */
    private bool $shouldReplayUpdaters = false;

    /**
     * A scoped container allowing to load Updater services.
     */
    private ?ContainerInterface $updaterLocator;

    public function __construct(?ContainerInterface $updaterLocator = null)
    {
        $this->updaterLocator = $updaterLocator;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function setShouldReplayUpdaters(bool $shouldReplayUpdaters): void
    {
        $this->shouldReplayUpdaters = $shouldReplayUpdaters;
    }

    public function shouldReplayUpdaters(): bool
    {
        return $this->shouldReplayUpdaters;
    }

    public function hasMigrations(): bool
    {
        return false;
    }

    public function hasFixtures(): bool
    {
        return false;
    }

    public function preInstall(): void
    {
    }

    public function postInstall(): void
    {
    }

    public function preUpdate(string $currentVersion, string $targetVersion): void
    {
        /** @var UpdaterExecutionRepository $updaterExecutionRepository */
        $updaterExecutionRepository = $this->container->get(ObjectManager::class)->getRepository(UpdaterExecution::class);

        foreach (static::getUpdaters() as $version => $updaterClass) {
            if (!version_compare($currentVersion, $version, '<')) {
                continue;
            }

            $hasBeenExecuted = $updaterExecutionRepository->hasBeenExecuted($updaterClass);
            if ($hasBeenExecuted && (!$this->shouldReplayUpdaters() || \is_subclass_of($updaterClass, NonReplayableUpdaterInterface::class))) {
                $this->logger->info(sprintf('Skipping "%s" because it has been already executed.', $updaterClass));
                continue;
            }

            $this->logger->info(sprintf('Executing "%s" preUpdate.', $updaterClass));
            $updater = $this->updaterLocator->get($updaterClass);
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }
    }

    public function postUpdate(string $currentVersion, string $targetVersion): void
    {
        /** @var UpdaterExecutionRepository $updaterExecutionRepository */
        $updaterExecutionRepository = $this->container->get(ObjectManager::class)->getRepository(UpdaterExecution::class);

        foreach (static::getUpdaters() as $version => $updaterClass) {
            if (!version_compare($currentVersion, $version, '<')) {
                continue;
            }

            $alreadyExecuted = $updaterExecutionRepository->hasBeenExecuted($updaterClass);

            if (!$this->shouldReplayUpdaters() && $alreadyExecuted) {
                return;
            }

            $this->logger->info(sprintf('Executing "%s" postUpdate.', $updaterClass));
            $updater = $this->updaterLocator->get($updaterClass);
            $updater->setLogger($this->logger);
            $updater->postUpdate();

            if (!$alreadyExecuted) {
                $updaterExecutionRepository->markAsExecuted($updaterClass);
            }
        }
    }

    public function preUninstall(): void
    {
    }

    public function postUninstall(): void
    {
    }

    public function end(?string $currentVersion = null, ?string $targetVersion = null): void
    {
    }

    /**
     * Gets the list of Updaters to play when the plugin is updated.
     * Keys are the target version (e.g. 15.0.0) and value is FQCN of the updater.
     *
     * NB. Updaters MUST extend Claroline\InstallationBundle\Updater\Updater and tagged with "claroline.platform.updater".
     */
    public static function getUpdaters(): array
    {
        return [];
    }
}

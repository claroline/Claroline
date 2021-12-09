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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Update\UpdaterExecution;
use Claroline\InstallationBundle\Repository\UpdaterExecutionRepository;
use Claroline\InstallationBundle\Updater\NonReplayableUpdaterInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AdditionalInstaller implements LoggerAwareInterface, ContainerAwareInterface, AdditionalInstallerInterface
{
    use LoggableTrait;
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var bool whether updaters should be executed even if they have been already
     */
    private $shouldReplayUpdaters = false;

    /**
     * @var ContainerInterface|null a scoped container allowing to load Updater services
     */
    private $updaterLocator;

    public function __construct(ContainerInterface $updaterLocator = null)
    {
        $this->updaterLocator = $updaterLocator;
    }

    public function setEnvironment($environment)
    {
        $this->environment = $environment;
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
        return true;
    }

    public function hasFixtures(): bool
    {
        return false;
    }

    public function preInstall()
    {
    }

    public function postInstall()
    {
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
        /** @var UpdaterExecutionRepository $updaterExecutionRepository */
        $updaterExecutionRepository = $this->container->get(ObjectManager::class)->getRepository(UpdaterExecution::class);

        foreach (static::getUpdaters() as $version => $updaterClass) {
            if (!version_compare($currentVersion, $version, '<')) {
                continue;
            }

            $hasBeenExecuted = $updaterExecutionRepository->hasBeenExecuted($updaterClass);
            if ($hasBeenExecuted && (!$this->shouldReplayUpdaters() || \is_subclass_of($updaterClass, NonReplayableUpdaterInterface::class))) {
                $this->log(sprintf('Skipping "%s" because it has been already executed.', $updaterClass));
                continue;
            }

            $updater = $this->updaterLocator->get($updaterClass);
            $updater->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
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

            $updater = $this->updaterLocator->get($updaterClass);
            $updater->postUpdate();

            if (!$alreadyExecuted) {
                $updaterExecutionRepository->markAsExecuted($updaterClass);
            }
        }
    }

    public function preUninstall()
    {
    }

    public function postUninstall()
    {
    }

    public function end($currentVersion, $targetVersion)
    {
    }

    public static function getUpdaters(): array
    {
        return [];
    }
}

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
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CoreBundle\Repository\DataSourceRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DataSourceManager
{
    private DataSourceRepository $dataSourceRepository;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly PluginManager $pluginManager
    ) {
        $this->dataSourceRepository = $om->getRepository(DataSource::class);
    }

    /**
     * Get the list of available sources in the platform.
     *
     * @param string $context
     *
     * @return array
     */
    public function getAvailable($context = null)
    {
        $enabledPlugins = $this->pluginManager->getEnabled();

        return $this->dataSourceRepository->findAllAvailable($enabledPlugins, $context);
    }

    /**
     * Checks if a data source exists and is available for the given context.
     */
    public function check(string $type, string $context): bool
    {
        /** @var DataSource $dataSource */
        $dataSource = $this->dataSourceRepository->findOneBy(['name' => $type]);
        if (!$dataSource) {
            // unknown data source
            return false;
        }

        if (!in_array($context, $dataSource->getContext())) {
            return false;
        }

        return true;
    }

    /**
     * Loads data from a data source.
     */
    public function load(string $type, string $context, ?string $contextId = null, array $options = null): mixed
    {
        $user = null;
        if ($this->tokenStorage->getToken()?->getUser() instanceof User) {
            $user = $this->tokenStorage->getToken()?->getUser();
        }

        $workspace = null;
        if (DataSource::CONTEXT_WORKSPACE === $context) {
            $workspace = $this->om
                ->getRepository(Workspace::class)
                ->findOneBy(['uuid' => $contextId]);
        }

        $event = new GetDataEvent($context, $options, $user, $workspace);
        $this->eventDispatcher->dispatch($event, 'data_source.'.$type.'.load');

        return $event->getData();
    }
}

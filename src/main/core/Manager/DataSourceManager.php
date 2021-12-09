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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CoreBundle\Repository\DataSourceRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DataSourceManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObjectManager */
    private $om;

    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var DataSourceRepository */
    private $dataSourceRepository;

    /** @var PluginManager */
    private $pluginManager;

    /**
     * WidgetManager constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        PluginManager $pluginManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->pluginManager = $pluginManager;
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
     *
     * @param string $contextId
     * @param array  $options
     *
     * @return mixed
     */
    public function load(string $type, string $context, $contextId = null, array $options = null)
    {
        $user = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        $workspace = null;
        if (DataSource::CONTEXT_WORKSPACE === $context) {
            $workspace = $this->om
                ->getRepository(Workspace::class)
                ->findOneBy(['uuid' => $contextId]);
        }

        /** @var GetDataEvent $event */
        $event = $this->eventDispatcher->dispatch(
            'data_source.'.$type.'.load',
            GetDataEvent::class,
            [$context, $options, $user, $workspace]
        );

        return $event->getData();
    }
}

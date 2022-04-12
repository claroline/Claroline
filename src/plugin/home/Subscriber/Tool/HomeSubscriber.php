<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Subscriber\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Home tool.
 */
class HomeSubscriber implements EventSubscriberInterface
{
    const NAME = 'home';

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var HomeManager */
    private $manager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        Crud $crud,
        HomeManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::DESKTOP, static::NAME) => 'onOpenDesktop',
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, static::NAME) => 'onOpenWorkspace',
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::ADMINISTRATION, static::NAME) => 'onOpenAdministration',
            ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, static::NAME) => 'onExportWorkspace',
            ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, static::NAME) => 'onImportWorkspace',
        ];
    }

    /**
     * Displays home on Desktop.
     */
    public function onOpenDesktop(OpenToolEvent $event)
    {
        $user = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
        }

        $event->setData([
            'tabs' => $this->manager->getDesktopTabs($user),
        ]);

        $event->stopPropagation();
    }

    /**
     * Displays home on Workspace.
     */
    public function onOpenWorkspace(OpenToolEvent $event)
    {
        $event->setData([
            'tabs' => $this->manager->getWorkspaceTabs($event->getWorkspace()),
        ]);

        $event->stopPropagation();
    }

    /**
     * Displays home administration tool.
     */
    public function onOpenAdministration(OpenToolEvent $event)
    {
        $event->setData([
            'administration' => true,
            'tabs' => $this->manager->getAdministrationTabs(),
        ]);

        $event->stopPropagation();
    }

    public function onExportWorkspace(ExportToolEvent $event)
    {
        $event->setData([
            'tabs' => $this->manager->getWorkspaceTabs($event->getWorkspace()),
        ]);
    }

    public function onImportWorkspace(ImportToolEvent $event)
    {
        $data = $event->getData();
        if (empty($data['tabs'])) {
            return;
        }

        $this->om->startFlushSuite();
        foreach ($data['tabs'] as $tab) {
            if (isset($tab['workspace'])) {
                unset($tab['workspace']);
            }

            $new = new HomeTab();
            $new->setWorkspace($event->getWorkspace());

            $this->crud->create($new, $tab, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            $event->addCreatedEntity($tab['id'], $new);
        }
        $this->om->endFlushSuite();
    }
}

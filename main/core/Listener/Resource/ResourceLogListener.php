<?php

namespace Claroline\CoreBundle\Listener\Resource;

/**
 * Logs actions on resources.
 *
 * @DI\Inject
 */
class ResourceLogListener
{
    private $dispatcher;

    /**
     * ResourceLogListener constructor.
     */
    public function __construct()
    {

    }

    /**
     * @DI\Observe("resource.open")
     */
    public function onOpen()
    {
        //$this->dispatcher->dispatch('log', 'Log\LogResourceRead', [$node]);
    }

    /**
     * @DI\Observe("resource.create")
     */
    public function onCreate()
    {
        /*$usersToNotify = $workspace && $workspace->getId() ?
            $this->container->get('claroline.manager.user_manager')->getUsersByWorkspaces([$workspace], null, null, false) :
            [];

        $this->eventDispatcher->dispatch('log', 'Log\LogResourceCreate', [$node, $usersToNotify]);
        $this->eventDispatcher->dispatch('log', 'Log\LogResourcePublish', [$node, $usersToNotify]);*/
    }

    /**
     * @DI\Observe("resource.copy")
     */
    public function onCopy()
    {
        // $this->dispatcher->dispatch('log', 'Log\LogResourceCopy', [$newNode, $node]);
    }

    /**
     * @DI\Observe("resource.toggle-publication")
     */
    public function onTogglePublication()
    {
        /*$usersToNotify = $node->getWorkspace() && !$node->getWorkspace()->isDisabledNotifications() ?
            $this->container->get('claroline.manager.user_manager')->getUsersByWorkspaces([$node->getWorkspace()], null, null, false) :
            [];

        $this->dispatcher->dispatch('log', 'Log\LogResourcePublish', [$node, $usersToNotify]);*/
    }

    /**
     * @DI\Observe("resource.configure")
     */
    public function onConfigure()
    {
        /*$uow = $this->om->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($node);

        if (count($changeSet) > 0) {
            $this->dispatcher->dispatch(
                'log',
                'Log\LogResourceUpdate',
                [$node, $changeSet]
            );
        }*/
    }

    /**
     * @DI\Observe("resource.move")
     */
    public function onMove()
    {
        // $this->dispatcher->dispatch('log', 'Log\LogResourceMove', [$child, $parent]);
    }

    /**
     * @DI\Observe("resource.delete")
     */
    public function onDelete()
    {
        /*$this->dispatcher->dispatch(
            'log',
            'Log\LogResourceDelete',
            [$node]
        );*/
    }

    /**
     * @DI\Observe("resource.export")
     */
    public function onExport()
    {
        //$this->dispatcher->dispatch('log', 'Log\LogResourceExport', [$node]);
    }
}

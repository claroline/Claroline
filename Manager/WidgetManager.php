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

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.widget_manager")
 */
class WidgetManager
{
    private $om;
    private $widgetInstanceRepo;
    private $widgetRepo;
    private $router;
    private $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"     = @DI\Inject("router"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(ObjectManager $om, RouterInterface $router, Translator $translator)
    {
        $this->om = $om;
        $this->widgetInstanceRepo = $om->getRepository('ClarolineCoreBundle:Widget\WidgetInstance');
        $this->widgetRepo = $om->getRepository('ClarolineCoreBundle:Widget\Widget');
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * Creates a widget instance.
     *
     * @param \Claroline\CoreBundle\Entity\Widget\Widget $widget
     * @param boolean $isAdmin
     * @param boolean $isDesktop
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $ws
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     *
     * @throws \Exception
     */
    public function createInstance(Widget $widget, $isAdmin, $isDesktop, User $user = null, AbstractWorkspace $ws = null)
    {
        if (!$widget->isDisplayableInDesktop()) {
            if ($isDesktop || $user) {
                throw new \Exception("This widget doesn't support the desktop");
            }
        }

        if (!$widget->isDisplayableInWorkspace()) {
            if (!$isDesktop || $ws) {
                throw new \Exception("This widget doesn't support the workspace");
            }
        }

        $instance = new WidgetInstance($widget);
        $instance->setName($this->translator->trans($widget->getName(), array(), 'widget'));
        $instance->setIsAdmin($isAdmin);
        $instance->setIsDesktop($isDesktop);
        $instance->setWidget($widget);
        $instance->setUser($user);
        $instance->setWorkspace($ws);
        $this->om->persist($instance);
        $this->om->flush();

        return $instance;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     */
    public function removeInstance(WidgetInstance $widgetInstance)
    {
        $this->om->remove($widgetInstance);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     */
    public function insertWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->om->persist($widgetInstance);
        $this->om->flush();
    }

    /**
     * Finds all widgets.
     *
     * @return \Claroline\CoreBundle\Entity\Widget\Widget
     */
    public function getAll()
    {
        return  $this->widgetRepo->findAll();
    }

    /**
     * Finds all widgets displayable in the desktop.
     *
     * @return \Claroline\CoreBundle\Entity\Widget\Widget
     */
    public function getDesktopWidgets()
    {
        return $this->widgetRepo->findBy(array('isDisplayableInDesktop' => true));
    }

    /**
     * Finds all widgets displayable in a workspace.
     *
     * @return \Claroline\CoreBundle\Entity\Widget\Widget
     */
    public function getWorkspaceWidgets()
    {
        return $this->widgetRepo->findBy(array('isDisplayableInWorkspace' => true));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getDesktopInstances(User $user)
    {
        return  $this->widgetInstanceRepo->findBy(array('user' => $user));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getWorkspaceInstances(AbstractWorkspace $workspace)
    {
        return  $this->widgetInstanceRepo->findBy(array('workspace' => $workspace));
    }

    /**
     * @todo define what I do
     *
     * @param array $excludedWidgetInstances
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getAdminDesktopWidgetInstance(array $excludedWidgetInstances)
    {
        if (count($excludedWidgetInstances) === 0) {

            return $this->widgetInstanceRepo->findBy(
                array(
                    'isAdmin' => true,
                    'isDesktop' => true
                )
            );
        }

        return $this->widgetInstanceRepo
            ->findAdminDesktopWidgetInstance($excludedWidgetInstances);
    }

    /**
     * @todo define what I do
     *
     * @param array $excludedWidgetInstances
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getAdminWorkspaceWidgetInstance(array $excludedWidgetInstances)
    {
        if (count($excludedWidgetInstances) === 0) {

            return $this->widgetInstanceRepo->findBy(
                array(
                    'isAdmin' => true,
                    'isDesktop' => false
                )
            );
        }

        return $this->widgetInstanceRepo
            ->findAdminWorkspaceWidgetInstance($excludedWidgetInstances);
    }

    /**
     * @todo define what I do
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param array $excludedWidgetInstances
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getDesktopWidgetInstance(
        User $user,
        array $excludedWidgetInstances
    )
    {
        if (count($excludedWidgetInstances) === 0) {

            return $this->widgetInstanceRepo->findBy(
                array(
                    'user' => $user,
                    'isAdmin' => false,
                    'isDesktop' => true
                )
            );
        }

        return $this->widgetInstanceRepo
            ->findDesktopWidgetInstance($user, $excludedWidgetInstances);
    }

    /**
     * @todo define what I do
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param array $excludedWidgetInstances
     *
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance[]
     */
    public function getWorkspaceWidgetInstance(
        AbstractWorkspace $workspace,
        array $excludedWidgetInstances
    )
    {
        if (count($excludedWidgetInstances) === 0) {

            return $this->widgetInstanceRepo->findBy(
                array(
                    'workspace' => $workspace,
                    'isAdmin' => false,
                    'isDesktop' => false
                )
            );
        }

        return $this->widgetInstanceRepo
            ->findWorkspaceWidgetInstance($workspace, $excludedWidgetInstances);
    }
}

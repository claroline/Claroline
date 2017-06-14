<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/12/17
 */

namespace Claroline\ExternalSynchronizationBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\RenderExternalGroupsButtonEvent;
use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use Claroline\ExternalSynchronizationBundle\Manager\ExternalSynchronizationManager;
use Claroline\ExternalSynchronizationBundle\Manager\ExternalSynchronizationUserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ExternalUserGroupSynchronizationListener.
 *
 * @DI\Service()
 */
class ExternalSynchronizationListener
{
    private $translator;
    private $templating;
    private $externalSyncManager;
    private $externalSyncUserManager;

    /**
     * ExternalUserGroupSynchronizationListener constructor.
     *
     * @param TranslatorInterface                $translator
     * @param TwigEngine                         $templating
     * @param ExternalSynchronizationManager     $externalSyncManager
     * @param ExternalSynchronizationUserManager $externalSyncUserManager
     *
     * @DI\InjectParams({
     *     "translator"                 = @DI\Inject("translator"),
     *     "templating"                 = @DI\Inject("templating"),
     *     "externalSyncManager"        = @DI\Inject("claroline.manager.external_user_group_sync_manager"),
     *     "externalSyncUserManager"    = @DI\Inject("claroline.manager.external_user_sync_manager")
     * })
     */
    public function __construct(
        TranslatorInterface $translator,
        TwigEngine $templating,
        ExternalSynchronizationManager $externalSyncManager,
        ExternalSynchronizationUserManager $externalSyncUserManager
    ) {
        $this->translator = $translator;
        $this->templating = $templating;
        $this->externalSyncManager = $externalSyncManager;
        $this->externalSyncUserManager = $externalSyncUserManager;
    }

    /**
     * @DI\Observe("claroline_external_parameters_menu_configure")
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     *
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onExternalAuthenticationMenuConfigure(ConfigureMenuEvent $event)
    {
        $name = $this->translator->trans('external_user_group_parameters', [], 'claro_external_user_group');
        $menu = $event->getMenu();
        $menu->addChild(
            $name,
            [
                'route' => 'claro_admin_external_sync_config_index',
            ]
        )->setExtra('name', $name);

        return $menu;
    }

    /**
     * @DI\Observe("claroline_external_sync_groups_button_render")
     *
     * @param RenderExternalGroupsButtonEvent $event
     */
    public function onExternalSynchronizationGroupsButtonRender(RenderExternalGroupsButtonEvent $event)
    {
        $sources = $this->externalSyncManager->getExternalSourcesNames(['group_config']);
        if (!empty($sources)) {
            $content = $this->templating->render(
                'ClarolineExternalSynchronizationBundle:Groups:externalGroupsButton.html.twig',
                ['workspace' => $event->getWorkspace()]
            );
            $event->addContent($content);
        }
    }

    /**
     * @DI\Observe("log")
     *
     * @param LogGenericEvent $event
     */
    public function onDeleteUser(LogGenericEvent $event)
    {
        if ($event instanceof LogUserDeleteEvent) {
            $receiver = $event->getReceiver();
            if ($receiver !== null) {
                $this->externalSyncUserManager->deleteExternalUserByUserId($receiver->getId());
            }
        }
    }
}

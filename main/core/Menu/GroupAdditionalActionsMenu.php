<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Menu;

use JMS\DiExtraBundle\Annotation as DI;
use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\ListRenderer;

/**
 * Class GroupAdditionalActionsMenu.
 *
 * @DI\Service("claroline.menu.group_additional_actions_renderer")
 * @DI\Tag("knp_menu.renderer", attributes = {"name" = "knp_menu.renderer", "alias"="group_additional_actions"})
 */
class GroupAdditionalActionsMenu extends ListRenderer
{
    /**
     * @DI\InjectParams({
     *     "matcher"        = @DI\Inject("knp_menu.matcher"),
     *     "defaultOptions" = @DI\Inject("%knp_menu.renderer.list.options%"),
     *     "charset"        = @DI\Inject("%kernel.charset%")
     * })
     */
    public function __construct(
        $matcher,
        $defaultOptions,
        $charset
    ) {
        $defaultOptions['leaf_class'] = $defaultOptions['branch_class'] = 'btn btn-default group-additional-action';
        parent::__construct($matcher, $defaultOptions, $charset);
    }

    protected function renderLinkElement(ItemInterface $item, array $options)
    {
        $uri = $item->getExtra('href') ? $item->getExtra('href') : $item->getUri();
        $displayMode = $item->getExtra('display') ? $item->getExtra('display') : 'normal';

        return sprintf(
            '<i class="%s group-action" data-url="%s" data-toggle="tooltip" data-placement="left" title="%s" data-display-mode="%s"></i>',
            $item->getExtra('icon'),
            $this->escape($uri),
            $this->renderLabel($item, $options),
            $displayMode
        );
    }

    protected function renderSpanElement(ItemInterface $item, array $options)
    {
        return $this->renderLinkElement($item, $options);
    }
}

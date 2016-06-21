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
use Knp\Menu\Renderer\ListRenderer;
use Knp\Menu\ItemInterface;

/**
 * @DI\Service("claroline.menu.top_bar_right_renderer")
 * @DI\Tag("knp_menu.renderer", attributes = {"name" = "knp_menu.renderer", "alias"="top_bar_right"})
 */
class TopBarRightRenderer extends ListRenderer
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
        parent::__construct($matcher, $defaultOptions, $charset);
    }

    protected function renderLinkElement(ItemInterface $item, array $options)
    {
        return sprintf(
            '<a role="menuitem" href="%s"><i class="%s"></i><span> %s</span></a>',
            $this->escape($item->getUri().$item->getExtra('uri-add')),
            $item->getExtra('icon'),
            $this->renderLabel($item, $options)
        );
    }
}

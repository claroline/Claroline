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
 * @DI\Service("claroline.menu.top_bar_left_renderer")
 * @DI\Tag("knp_menu.renderer", attributes = {"name" = "knp_menu.renderer", "alias"="top_bar_left"})
 */
class TopBarLeftRenderer extends ListRenderer
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
        $uri = $item->getExtra('href') ? $item->getExtra('href') : $item->getUri();

        return sprintf(
            '<a role="menuitem" href="%s" title="%s"><i class="%s"></i><span class="break-hide"> %s</span> %s</a> %s',
            $this->escape($uri),
            $item->getExtra('title'),
            $item->getExtra('icon'),
            $this->renderLabel($item, $options),
            !empty($item->getExtra('badge')) ? sprintf('<span class="badge">%s</span>', $item->getExtra('badge')) : '',
            !empty($item->getExtra('close')) ? sprintf('<div>%s</div>', $item->getExtra('close')) : ''
        );
    }

    protected function renderSpanElement(ItemInterface $item, array $options)
    {
        return $this->renderLinkElement($item, $options);
    }
}

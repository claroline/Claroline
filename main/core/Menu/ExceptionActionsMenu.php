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
 * Class ExceptionActionsMenu.
 *
 * @DI\Service("claroline.menu.exception_actions_renderer")
 * @DI\Tag("knp_menu.renderer", attributes = {"name" = "knp_menu.renderer", "alias"="exception_actions"})
 */
class ExceptionActionsMenu extends ListRenderer
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

    protected function renderItem(ItemInterface $item, array $options)
    {
        return $this->renderLink($item, $options);
    }

    protected function renderLinkElement(ItemInterface $item, array $options)
    {
        $uri = $item->getExtra('href') ? $item->getExtra('href') : $item->getUri();
        $displayMode = $item->getExtra('display') ? $item->getExtra('display') : 'normal';

        return sprintf(
            '<span class="btn btn-danger btn-lg exception-action-btn text-center" data-url="%s" data-display-mode="%s">'.
            '%s'.
            '</span>',
//            $item->getExtra('icon'),
            $this->escape($uri),
            $displayMode,
            $this->renderLabel($item, $options)
        );
    }

    protected function renderSpanElement(ItemInterface $item, array $options)
    {
        return $this->renderLinkElement($item, $options);
    }

    protected function renderList(ItemInterface $item, array $attributes, array $options)
    {
        return $this->renderChildren($item, $options);
    }
}

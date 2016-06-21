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
 * @DI\Service("claroline.menu.external_authentication_renderer")
 * @DI\Tag("knp_menu.renderer", attributes = {"name" = "knp_menu.renderer", "alias"="external_authentication_renderer"})
 */
class ExternalAuthenticationRenderer extends ListRenderer
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
            '<a href="%s" class="list-group-item">%s</a>',
            $this->escape($item->getUri()),
            $this->renderLabel($item, $options)
        );
    }

    protected function renderItem(ItemInterface $item, array $options)
    {
        return $this->renderLink($item, $options);
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

<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/7/15
 */

namespace Claroline\CoreBundle\Menu;

use JMS\DiExtraBundle\Annotation as DI;
use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\ListRenderer;

/**
 * Class DesktopParametersMenu.
 *
 * @DI\Service("claroline.menu.desktop_parameters_renderer")
 * @DI\Tag("knp_menu.renderer", attributes = {"name" = "knp_menu.renderer", "alias"="desktop_parameters"})
 */
class DesktopParametersMenu extends ListRenderer
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
        $defaultOptions['leaf_class'] = $defaultOptions['branch_class'] = 'list-group-item';
        parent::__construct($matcher, $defaultOptions, $charset);
    }

    protected function renderLinkElement(ItemInterface $item, array $options)
    {
        $uri = $item->getExtra('href') ? $item->getExtra('href') : $item->getUri();

        return sprintf(
            '<a href="%s">%s</a>',
            $this->escape($uri),
            $this->renderLabel($item, $options)
        );
    }

    protected function renderSpanElement(ItemInterface $item, array $options)
    {
        return $this->renderLinkElement($item, $options);
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Templating\Loader;

use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Manager\Theme\ThemeManager;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator as BaseTemplateLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * {@inheritdoc}
 */
class TemplateLocator extends BaseTemplateLocator
{
    private $themeManager;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface $locator
     * @param ThemeManager         $themeManager
     * @param string               $cacheDir
     */
    public function __construct(
        FileLocatorInterface $locator,
        ThemeManager $themeManager,
        $cacheDir = null
    ) {
        parent::__construct($locator, $cacheDir);

        $this->themeManager = $themeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function locate($template, $currentPath = null, $first = true)
    {
        if (!$template instanceof TemplateReferenceInterface) {
            throw new \InvalidArgumentException('The template must be an instance of TemplateReferenceInterface.');
        }

        $theme = $this->themeManager->getCurrentTheme();

        if (!$theme) {
            // no custom localization if no theme (e.g. in test environment)
            return parent::locate($template, $currentPath, $first);
        }

        $bundle = $this->getBundle($theme);
        $template = $this->locateTemplate($template, $bundle, $theme, $currentPath);
        $key = $this->getCacheKey($template);

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        try {
            return $this->cache[$key] = $this->locator->locate($template->getPath(), $currentPath);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                sprintf('Unable to find template "%s" : "%s".', $template, $e->getMessage()), 0, $e
            );
        }
    }

    /**
     * @param TemplateReferenceInterface                    $template
     * @param string                                        $bundle
     * @param \Claroline\CoreBundle\Entity\Theme\Theme|null $theme
     * @param string                                        $currentPath
     *
     * @return TemplateReferenceInterface
     */
    private function locateTemplate(TemplateReferenceInterface $template, $bundle, $theme, $currentPath)
    {
        $newTemplate = clone $template;
        $controller = $template->get('controller');

        if (null !== $theme) {
            if ($controller) {
                $controller = sprintf(
                    'theme/%s/%s',
                    $template->get('bundle'),
                    $template->get('controller')
                );
            } else {
                $controller = sprintf(
                    'theme/%s',
                    $template->get('bundle')
                );
            }
        }

        $newTemplate->set('bundle', $bundle)->set('controller', $controller);

        try {
            $this->locator->locate($newTemplate->getPath(), $currentPath);
        } catch (\Exception $ex) {
            $newTemplate = $template;
        }

        return $newTemplate;
    }

    private function getBundle(Theme $theme)
    {
        $plugin = $theme->getPlugin();

        return $plugin ? $plugin->getSfName() : 'ClarolineCoreBundle';
    }
}

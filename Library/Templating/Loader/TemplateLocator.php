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

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Themes\ThemeService;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator as baseTemplateLocator;

/**
 * {@inheritDoc}
 */
class TemplateLocator extends baseTemplateLocator
{
    protected $locator;
    protected $cache;
    protected $configHandler;
    protected $themeService;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface         $locator       A FileLocatorInterface instance
     * @param PlatformConfigurationHandler $configHandler Claroline platform configuration handler service
     * @param ThemeService                 $themeService  Claroline theme service
     * @param string                       $cacheDir      The cache path
     */
    public function __construct(
        FileLocatorInterface $locator,
        PlatformConfigurationHandler $configHandler,
        ThemeService $themeService, $cacheDir = null
    )
    {
        if (null !== $cacheDir && is_file($cache = $cacheDir.'/templates.php')) {
            $this->cache = require $cache;
        }

        $this->locator = $locator;
        $this->configHandler = $configHandler;
        $this->themeService = $themeService;
    }

    /**
     * {@inheritDoc}
     */
    public function locate($template, $currentPath = null, $first = true)
    {
        if (!$template instanceof TemplateReferenceInterface) {
            throw new \InvalidArgumentException('The template must be an instance of TemplateReferenceInterface.');
        }

        $path = $this->configHandler->getParameter('theme');
        $theme = $this->themeService->findTheme(array('path' => $path));
        $bundle = substr($path, 0, strpos($path, ':'));

        if ($theme !== null and
            $bundle !== '' and
            $bundle !== $template->get('bundle') and
            $template->get('bundle') === 'ClarolineCoreBundle'
        ) {
            $template = $this->locateTemplate($template, $bundle, $theme, $currentPath);
        } elseif ($template->get('bundle') === 'FOSOAuthServerBundle') {
            if ('Authorize' === $template->get('controller') && 'authorize' === $template->get('name')) {
                $template = $this->locateTemplate($template, 'ClarolineCoreBundle', $theme, $currentPath);
            }
        }

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
        $controller  = $template->get('controller');

        if (null !== $theme) {
            $controller = sprintf(
                '%s/%s',
                strtolower(str_replace(' ', '', $theme->getName())),
                $template->get('controller')
            );
        }

        $newTemplate
            ->set('bundle', $bundle)
            ->set('controller', $controller);

        try {
            $this->locator->locate($newTemplate->getPath(), $currentPath);
        } catch (\Exception $e) {
            $newTemplate = $template;
        }

        return $newTemplate;
    }
}

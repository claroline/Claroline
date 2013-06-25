<?php

namespace Claroline\CoreBundle\Library\Templating\Loader;

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
     * @param FileLocatorInterface          $locator  A FileLocatorInterface instance
     * @param PlatformConfigurationHandler  $configHandler Claroline platform configuration handler service
     * @param ThemeService                  $themeService Claroline theme service
     * @param string                        $cacheDir The cache path
     */
    public function __construct(FileLocatorInterface $locator, $configHandler, $themeService, $cacheDir = null)
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

        if (is_object($theme) and
            $bundle !== '' and
            $bundle !== $template->get('bundle') and
            $template->get('bundle') === 'ClarolineCoreBundle') {
            $tmp = clone $template;

            $template->set('bundle', $bundle);
            $template->set(
                'controller',
                strtolower(str_replace(' ', '', $theme->getName())).'/'.$template->get('controller')
            );

            try {
                $this->locator->locate($template->getPath(), $currentPath);
            } catch (\InvalidArgumentException $e) {
                $template = $tmp; //return to default
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
}


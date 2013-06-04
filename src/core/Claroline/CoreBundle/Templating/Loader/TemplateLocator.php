<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Templating\Loader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator as baseTemplateLocator;

/**
 * TemplateLocator locates templates in bundles.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TemplateLocator extends baseTemplateLocator
{
    protected $locator;
    protected $cache;
    protected $configHandler;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface $locator  A FileLocatorInterface instance
     * @param string               $cacheDir The cache path
     */
    public function __construct(FileLocatorInterface $locator, $cacheDir = null, $configHandler)
    {
        if (null !== $cacheDir && is_file($cache = $cacheDir.'/templates.php')) {
            $this->cache = require $cache;
        }

        $this->locator = $locator;
        $this->configHandler = $configHandler;
    }

    /**
     * Returns a full path for a given file.
     *
     * @param TemplateReferenceInterface $template A template
     *
     * @return string The full path for the file
     */
    protected function getCacheKey($template)
    {
        return $template->getLogicalName();
    }

    /**
     * Returns a full path for a given file.
     *
     * @param TemplateReferenceInterface $template    A template
     * @param string                     $currentPath Unused
     * @param Boolean                    $first       Unused
     *
     * @return string The full path for the file
     *
     * @throws \InvalidArgumentException When the template is not an instance of TemplateReferenceInterface
     * @throws \InvalidArgumentException When the template file can not be found
     */
    public function locate($template, $currentPath = null, $first = true)
    {
        if (!$template instanceof TemplateReferenceInterface) {
            throw new \InvalidArgumentException("The template must be an instance of TemplateReferenceInterface.");
        }

        $theme = $this->configHandler->getParameter("theme");
        $theme = substr($theme, 0, strpos($theme, ":"));

        $tmp = $template->get("bundle");

        if (is_object($template) and $theme != "" and $theme != $tmp and $tmp == "ClarolineCoreBundle") {

            $template->set("bundle", $theme);

            try {
                $this->locator->locate($template->getPath(), $currentPath);
            } catch (\InvalidArgumentException $e) {
                $template->set("bundle", $tmp);
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


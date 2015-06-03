<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Service
 * @Tag("twig.extension")
 */
class PackageExtension extends \Twig_Extension
{
    private $vendorPath;
    private $translator;

    /**
     * @InjectParams({
     *     "vendorPath" = @Inject("%claroline.param.vendor_directory%"),
     *     "translator" = @Inject("translator")
     * })
     */
    public function __construct($vendorPath, $translator)
    {
        $this->vendorPath = $vendorPath;
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return array(
            'is_installation_requirement_satisfied' => new \Twig_Function_Method($this, 'isPackageInstallable'),
            'render_package_missing_permissions' => new \Twig_Function_Method($this, 'renderMissingPermissions'),
            'render_package_missing_require' => new \Twig_Function_Method($this, 'renderMissingPermissions')
        );
    }

    public function getName()
    {
        return 'package_extension';
    }

    public function isPackageInstallable($basePath)
    {
        $fullPath = realpath($this->vendorPath . '/' . $basePath);
        $basePath = realpath($this->vendorPath . '/' . substr($basePath, 0, strrpos($basePath, '/')));

        return ($fullPath && $basePath) ? is_writable($fullPath) & is_writable($basePath): is_writable($basePath);
    }

    public function renderMissingPermissions($basePath)
    {
        $fullPath = realpath($this->vendorPath . '/' . $basePath);
        $basePath = realpath($this->vendorPath . '/' . substr($basePath, 0, strrpos($basePath, '/')));
        $fullPathElement = $basePathElement = '';
        $notWritable = $this->translator->trans('is_not_writable', array(), 'platform');

        if (!is_writable($fullPath) && $fullPath) {
            $fullPathElement = "<li class='alert alert-danger'>" . $fullPath . ' ' . $notWritable . "</li>";
        }

        if (!is_writable($basePath) && $basePath) {
            $basePathElement = "<li class='alert alert-danger'>" . $basePath . ' ' . $notWritable . "</li>";
        }

        $rendering = sprintf(
            '<ul>%s%s</ul>',
            $fullPathElement,
            $basePathElement
        );

        return $rendering;
    }

    public function renderMissingRequire($require)
    {

    }
}

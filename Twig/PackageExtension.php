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
use Claroline\CoreBundle\Library\Utilities\FileSystem;

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
            'render_package_missing_require' => new \Twig_Function_Method($this, 'renderMissingRequire')
        );
    }

    public function getName()
    {
        return 'package_extension';
    }

    public function isPackageInstallable($basePath, $requirements = null)
    {
        $fs = new FileSystem();
        $fullPath = realpath($this->vendorPath . '/' . $basePath);
        $basePath = realpath($this->vendorPath . '/' . substr($basePath, 0, strrpos($basePath, '/')));
        $requirementsMet = true;
        $isWritable = true;

        if ($fullPath && $basePath) {
            $isWritable = $fs->isWritable($fullPath, true) & is_writable($basePath);
        } else {
            if ($fullPath) $isWritable = is_writable($fullPath);
            if ($basePath) $isWritable = is_writable($basePath);
        }

        $missingsExtensions = $this->findMissingRequireExtensions($requirements);
        if (count($missingsExtensions) >= 1) $requirementsMet = false;

        $missingsBundles = $this->findMissingRequireBundle($requirements);
        if (count($missingsBundles) >= 1) $requirementsMet = false;

        return $isWritable & $requirementsMet;
    }

    public function renderMissingPermissions($basePath)
    {
        $fs = new FileSystem();
        $fullPath = realpath($this->vendorPath . '/' . $basePath);
        $basePath = realpath($this->vendorPath . '/' . substr($basePath, 0, strrpos($basePath, '/')));
        $fullPathElement = $basePathElement = '';
        $notWritable = $this->translator->trans('is_not_writable', array(), 'platform');
        $notWritableRecursive = $this->translator->trans('is_not_writable_recursive', array(), 'platform');

        if ($fullPath && !$fs->isWritable($fullPath, true)) {
            $fullPathElement = "<li class='alert alert-danger'>" . $fullPath . ' ' . $notWritableRecursive . "</li>";
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

    public function renderMissingRequire($require = null)
    {
        $liExt = '';
        $liBundle = '';
        $missingExtensions = $this->findMissingRequireExtensions($require);

        foreach ($missingExtensions as $ext) {
            $arr = explode('ext-', $ext);
            $missingExtensionMsg = $this->translator->trans('ext_php_missing', array('%ext%' => $arr[1]), 'platform');
            $liExt .= sprintf("<li class='alert alert-danger'>%s</li>", $missingExtensionMsg);
        }

        $missingBundles = $this->findMissingRequireBundle($require);

        foreach ($missingBundles as $bundle) {
            $missingExtensionMsg = $this->translator->trans('claroline_bundle_missing', array('%bundle%' => $bundle), 'platform');
            $liExt .= sprintf("<li class='alert alert-danger'>%s</li>", $missingExtensionMsg);
        }

        $rendering = sprintf(
            '<ul>%s%s</ul>',
            $liExt,
            $liBundle
        );

        return $rendering;
    }

    //do not support version yet...
    private function findMissingRequireExtensions($object)
    {
        $missings = array();
        if ($object === null) return $missings;
        $requires = get_object_vars($object);

        //check php extension...
        foreach ($requires as $require => $version) {
            if (strpos($require, 'ext-') === 0) {
                $arr = explode('ext-', $require);
                if (!extension_loaded($arr[1])) {
                    $missings[] = $require;
                }
            }
        }

        return $missings;
    }

    private function findMissingRequireBundle($object)
    {
        $missings = array();
        if ($object === null) return $missings;
        $requires = get_object_vars($object);

        //check composer requirements...
        foreach ($requires as $require => $version) {
            if (strpos($require, 'ext-') !== 0 && strpos($require, 'php') !== 0) {
                //then we must check if the require is somewhere...
                if (!is_dir($this->vendorPath . '/' . $require)) {
                    $missings[] = $require;
                }
            }
        }

        return $missings;
    }
}

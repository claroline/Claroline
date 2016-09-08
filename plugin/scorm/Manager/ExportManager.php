<?php

namespace Claroline\ScormBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\ResourceNotFoundException;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Twig\WebpackExtension;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use Claroline\ScormBundle\Library\Export\Manifest\AbstractScormManifest;
use Claroline\ScormBundle\Library\Export\Manifest\Scorm12Manifest;
use Claroline\ScormBundle\Library\Export\Manifest\Scorm2004Manifest;
use FOS\JsRoutingBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Create Scorm packages from Claroline resources.
 *
 * @DI\Service("claroline.scorm.export_manager")
 */
class ExportManager
{
    /** @var string */
    private $tmpPath;

    /** @var string */
    private $webPath;

    /** @var string */
    private $uploadPath;

    /** @var RouterInterface */
    private $router;

    /** @var Controller */
    private $jsRouterCtrl;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var ClaroUtilities */
    private $utilities;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var WebpackExtension */
    private $webpack;

    /**
     * Constructor.
     *
     * @param string           $tmp
     * @param string           $rootDir
     * @param string           $uploadDir
     * @param RouterInterface  $router
     * @param Controller       $jsRouterCtrl
     * @param StrictDispatcher $dispatcher
     * @param ClaroUtilities   $utilities
     * @param ResourceManager  $resourceManager
     * @param WebpackExtension $webpack
     *
     * @DI\InjectParams({
     *     "tmp"             = @DI\Inject("%claroline.param.platform_generated_archive_path%"),
     *     "rootDir"         = @DI\Inject("%kernel.root_dir%"),
     *     "uploadDir"       = @DI\Inject("%claroline.param.files_directory%"),
     *     "router"          = @DI\Inject("router"),
     *     "jsRouterCtrl"    = @DI\Inject("fos_js_routing.controller"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "utilities"       = @DI\Inject("claroline.utilities.misc"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "webpack"         = @DI\Inject("claroline.extension.webpack")
     * })
     */
    public function __construct(
        $tmp,
        $rootDir,
        $uploadDir,
        RouterInterface $router,
        Controller $jsRouterCtrl,
        StrictDispatcher $dispatcher,
        ClaroUtilities $utilities,
        ResourceManager $resourceManager,
        WebpackExtension $webpack)
    {
        $this->tmpPath = $tmp;
        $this->webPath = $rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web';
        $this->uploadPath = $uploadDir;

        $this->router = $router;
        $this->jsRouterCtrl = $jsRouterCtrl;
        $this->dispatcher = $dispatcher;
        $this->utilities = $utilities;
        $this->resourceManager = $resourceManager;
        $this->webpack = $webpack;
    }

    /**
     * Create a Scorm archive for a ResourceNode.
     *
     * @param ResourceNode $node
     * @param string       $locale
     * @param string       $scormVersion
     *
     * @return \ZipArchive
     *
     * @throws ResourceNotFoundException
     * @throws \Exception
     */
    public function export(ResourceNode $node, $locale = 'en', $scormVersion = '2004')
    {
        if ('2004' !== $scormVersion && '1.2' !== $scormVersion) {
            // Invalid Scorm version
            throw new \Exception('SCORM export : Invalid SCORM version.');
        }

        $resource = $this->resourceManager->getResourceFromNode($node);
        if (!$resource) {
            throw new ResourceNotFoundException('SCORM export : The resource '.$node->getName().' was not found');
        }

        // Export the Resource and all it's sub-resources
        $exportedResources = $this->exportResource($resource, $locale);

        // Create the manifest for the Scorm package
        if ('1.2' === $scormVersion) {
            $manifest = new Scorm12Manifest($node, $exportedResources);
        } else {
            $manifest = new Scorm2004Manifest($node, $exportedResources);
        }

        $package = $this->createPackage($node, $locale, $manifest, $exportedResources);

        return $package;
    }

    /**
     * Export a Claroline Resource and all it's embed Resources.
     *
     * @param AbstractResource $resource
     * @param string           $locale
     *
     * @return array - The list of exported resource
     */
    private function exportResource(AbstractResource $resource, $locale)
    {
        $resources = [];

        $event = $this->dispatchEvent($resource, $locale);
        $embedResources = $event->getEmbedResources();

        // Grab data from event
        $resources[$resource->getResourceNode()->getId()] = [
            'node' => $resource->getResourceNode(),
            'template' => $event->getTemplate(),
            'assets' => $event->getAssets(),
            'files' => $event->getFiles(),
            'translation_domains' => $event->getTranslationDomains(),
            'resources' => array_keys($embedResources), // We only need IDs
        ];

        if (!empty($embedResources)) {
            foreach ($embedResources as $embedResource) {
                if (empty($resources[$embedResource->getResourceNode()->getId()])) {
                    // Current resource has not been exported yet
                    $exported = $this->exportResource($embedResource, $locale);
                    $resources = array_merge($resources, $exported);
                }
            }
        }

        return $resources;
    }

    /**
     * Dispatch export event for the Resource.
     *
     * @param AbstractResource $resource
     * @param string           $locale
     *
     * @return ExportScormResourceEvent
     */
    private function dispatchEvent(AbstractResource $resource, $locale)
    {
        return $this->dispatcher->dispatch(
            'export_scorm_'.$resource->getResourceNode()->getResourceType()->getName(),
            'Claroline\\ScormBundle\\Event\\ExportScormResourceEvent',
            [$resource, $locale]
        );
    }

    /**
     * Create SCORM package.
     *
     * @param ResourceNode          $node     - The exported ResourceNode
     * @param string                $locale   - THe locale to use for export
     * @param AbstractScormManifest $manifest - The manifest of the SCORM package
     * @param array                 $scos     - The list of resources to include into the package
     *
     * @return \ZipArchive
     */
    public function createPackage(ResourceNode $node, $locale, AbstractScormManifest $manifest, array $scos = [])
    {
        $scormId = 'scorm-'.$node->getId().'-'.date('YmdHis');

        // Create and open scorm archive
        if (!is_dir($this->tmpPath)) {
            mkdir($this->tmpPath);
        }

        $archive = new \ZipArchive();
        $archive->open($this->tmpPath.DIRECTORY_SEPARATOR.$scormId.'.zip', \ZipArchive::CREATE);

        // Add manifest
        $this->saveToPackage($archive, 'imsmanifest.xml', $manifest->dump());

        // Add common files
        $this->addCommons($archive, $locale);

        // Add resources files
        foreach ($scos as $sco) {
            // Dump template into file
            $this->saveToPackage($archive, 'scos/resource_'.$sco['node']->getId().'.html', $sco['template']);

            // Dump additional resource assets
            if (!empty($sco['assets'])) {
                foreach ($sco['assets'] as $filename => $originalFile) {
                    $this->copyToPackage($archive, 'assets/'.$filename, $this->getFilePath($this->webPath, $originalFile));
                }
            }

            // Add uploaded files
            if (!empty($sco['files'])) {
                // $this->container->getParameter('claroline.param.files_directory')
                foreach ($sco['files'] as $filename => $originalFile) {
                    $filePath = $originalFile['absolute'] ? $originalFile['path'] : $this->getFilePath($this->uploadPath, $originalFile['path']);
                    $this->copyToPackage($archive, 'files/'.$filename, $filePath);
                }
            }

            // Add translations
            if (!empty($sco['translation_domains'])) {
                foreach ($sco['translation_domains'] as $domain) {
                    $translationFile = 'js/translations/'.$domain.'/'.$locale.'.js';
                    $this->copyToPackage($archive, 'translations/'.$domain.'.js', $this->getFilePath($this->webPath, $translationFile));
                }
            }
        }

        $archive->close();

        return $archive;
    }

    /**
     * Adds the claroline common assets and translations into the package.
     *
     * @param \ZipArchive $archive
     * @param string      $locale
     */
    private function addCommons(\ZipArchive $archive, $locale)
    {
        $assets = [
            'bootstrap.css' => 'themes/claroline/bootstrap.css',
            'font-awesome.css' => 'packages/font-awesome/css/font-awesome.min.css',
            'claroline-reset.css' => 'vendor/clarolinescorm/claroline-reset.css',
            'jquery.min.js' => 'packages/jquery/dist/jquery.min.js',
            'jquery-ui.min.js' => 'packages/jquery-ui/jquery-ui.min.js',
            'bootstrap.min.js' => 'packages/bootstrap/dist/js/bootstrap.min.js',
            'translator.js' => 'bundles/bazingajstranslation/js/translator.min.js',
            'router.js' => 'bundles/fosjsrouting/js/router.js',
            'video.min.js' => 'packages/video.js/dist/video.min.js',
            'video-js.min.css' => 'packages/video.js/dist/video-js.min.css',
            'video-js.swf' => 'packages/video.js/dist/video-js.swf',
        ];

        $webpackAssets = [
            'commons.js' => 'dist/commons.js',
            'claroline-distribution-plugin-video-player-watcher.js' => 'dist/claroline-distribution-plugin-video-player-watcher.js',
        ];

        $translationDomains = [
            'resource',
            'home',
            'platform',
            'error',
            'validators',
        ];

        foreach ($assets as $filename => $originalFile) {
            $this->copyToPackage($archive, 'commons/'.$filename, $this->getFilePath($this->webPath, $originalFile));
        }

        // Add webpack assets
        foreach ($webpackAssets as $filename => $originalFile) {
            $this->copyToPackage(
                $archive,
                'commons/'.$filename,
                $this->getFilePath($this->webPath, $this->webpack->hotAsset($originalFile, true))
            );
        }

        // Add FontAwesome font files
        $fontDir = $this->webPath.DIRECTORY_SEPARATOR.'packages/font-awesome/fonts';
        $files = scandir($fontDir);
        foreach ($files as $file) {
            $filePath = $fontDir.DIRECTORY_SEPARATOR.$file;
            if (is_file($filePath)) {
                $this->copyToPackage($archive, 'fonts/'.$file, $this->getFilePath($fontDir, $file));
            }
        }

        // Generate JS routes with FOSJSRoutingBundle
        $request = new Request([
            'callback' => 'fos.Router.setData',
        ]);
        $jsRoutes = $this->jsRouterCtrl->indexAction($request, 'js');
        $this->saveToPackage($archive, 'commons/routes.js', $jsRoutes->getContent());

        // Add common translations
        foreach ($translationDomains as $domain) {
            $translationFile = 'js/translations/'.$domain.'/'.$locale.'.js';
            $this->copyToPackage($archive, 'translations/'.$domain.'.js', $this->getFilePath($this->webPath, $translationFile));
        }
    }

    /**
     * Copy file into the SCORM package.
     *
     * @param \ZipArchive $archive
     * @param string      $pathInArchive
     * @param string      $path
     */
    private function copyToPackage(\ZipArchive $archive, $pathInArchive, $path)
    {
        if (file_exists($path)) {
            if (is_dir($path)) {
                /** @var \SplFileInfo[] $files */
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $file) {
                    // Skip directories
                    if (!$file->isDir()) {
                        // Get real and relative path for current file
                        // Add current file to archive
                        $archive->addFile(
                            $path.DIRECTORY_SEPARATOR.$file->getFilename(),
                            $pathInArchive.DIRECTORY_SEPARATOR.$file->getFilename());
                    }
                }
            } else {
                $archive->addFile($path, $pathInArchive);
            }
        } else {
            throw new FileNotFoundException(sprintf('File "%s" could not be found.', $path));
        }
    }

    /**
     * Save content into the SCORM package.
     *
     * @param \ZipArchive $archive
     * @param string      $pathInArchive
     * @param string      $content
     */
    private function saveToPackage(\ZipArchive $archive, $pathInArchive, $content)
    {
        $archive->addFromString($pathInArchive, $content);
    }

    private function getFilePath($sourceDir, $relativePath)
    {
        $filePath = ltrim($relativePath, '/\\');
        $filePath = $sourceDir.DIRECTORY_SEPARATOR.$filePath;

        return $filePath;
    }
}

<?php

namespace Claroline\ScormBundle\Event;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Symfony\Component\EventDispatcher\Event;

class ExportScormResourceEvent extends Event implements DataConveyorEventInterface
{
    /**
     * Resource to export.
     *
     * @var AbstractResource
     */
    private $resource;

    /**
     * Locale used for the export.
     *
     * @var string
     */
    private $locale;

    /**
     * Template of the Resource (the only required property).
     *
     * @var string
     */
    private $template = null;

    /**
     * List of assets required by the Resource.
     *
     * @var array
     */
    private $assets = [];

    /**
     * List of uploaded files required by the Resource.
     *
     * @var array
     */
    private $files = [];

    /**
     * List of translation domains to include.
     *
     * @var array
     */
    private $translationDomains = [];

    /**
     * List of resource to export too.
     *
     * @var AbstractResource[]
     */
    private $embedResources = [];

    /**
     * @var bool
     */
    private $populated;

    /**
     * Constructor.
     *
     * @param AbstractResource $resource
     * @param string           $locale
     */
    public function __construct(AbstractResource $resource, $locale)
    {
        $this->resource = $resource;
        $this->locale = $locale;
    }

    /**
     * Get Resource.
     *
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get the template of the Resource.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the Resource template.
     *
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        $this->populated = true;
    }

    /**
     * Get assets of the Resource.
     *
     * @return array
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Add a new asset file to include.
     *
     * @param string $packageName - Name of the asset in the SCORM package (with extension)
     * @param string $webPath     - Relative path to the file inside the `web` directory
     */
    public function addAsset($packageName, $webPath)
    {
        $this->assets[$packageName] = $webPath;
    }

    /**
     * Get files of the Resource.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Add a new uploaded file to include.
     *
     * @param string $packageName  - Name of the asset in the SCORM package (with extension)
     * @param string $filePath     - Path to the file
     * @param bool   $absolutePath - if false $filePath will be searched in `files` directory
     */
    public function addFile($packageName, $filePath, $absolutePath = false)
    {
        $this->files[$packageName] = [
            'path' => $filePath,
            'absolute' => $absolutePath,
        ];
    }

    /**
     * Get translation domains needed by the Resource.
     *
     * @return array
     */
    public function getTranslationDomains()
    {
        return $this->translationDomains;
    }

    /**
     * Add a translation domain.
     *
     * @param $domain
     */
    public function addTranslationDomain($domain)
    {
        if (!in_array($domain, $this->translationDomains)) {
            $this->translationDomains[] = $domain;
        }
    }

    /**
     * Get all embed Resources.
     *
     * @return AbstractResource[]
     */
    public function getEmbedResources()
    {
        return $this->embedResources;
    }

    public function setEmbedResources(array $resources = [])
    {
        $this->embedResources = $resources;
    }

    /**
     * Add an embed Resource.
     *
     * @param AbstractResource $resource
     */
    public function addEmbedResource(AbstractResource $resource)
    {
        if (!in_array($resource, $this->embedResources)) {
            // Uses node ID as array key in order to avoid duplicates
            $this->embedResources[$resource->getResourceNode()->getId()] = $resource;
        }
    }

    /**
     * Are event data populated ?
     *
     * @return bool
     */
    public function isPopulated()
    {
        return $this->populated;
    }
}

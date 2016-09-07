<?php

namespace Claroline\ScormBundle\Library\Export\Manifest;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

abstract class AbstractScormManifest
{
    /**
     * The Node of the primary resource of the package.
     *
     * @var ResourceNode
     */
    protected $node;

    /**
     * The list of Resources (SCO) of the package.
     *
     * @var array
     */
    protected $resources;

    /**
     * XML representation of the manifest.
     *
     * @var \SimpleXMLElement
     */
    protected $xml;

    public function __construct(ResourceNode $node, array $resources = [])
    {
        $this->node = $node;
        $this->resources = $resources;

        // Create Manifest container
        $this->xml = new \SimpleXMLElement('<manifest></manifest>');
        $this->xml->addAttribute('identifier', 'scorm_'.$node->getId());

        $this->addMetadata();
        $this->addOrganizations();
        $this->addResources();
    }

    /**
     * Get SCORM schema version.
     *
     * @return string
     */
    abstract protected function getSchemaVersion();

    /**
     * Dump manifest structure into a XML string.
     *
     * @return string
     */
    public function dump()
    {
        // Create a new XML document
        $document = new \DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        $document->loadXML($this->xml->asXML());

        return $document->saveXML();
    }

    /**
     * Add metadata node to the manifest.
     *
     * @return \SimpleXMLElement
     */
    protected function addMetadata()
    {
        $metadata = $this->xml->addChild('metadata');
        $metadata->addChild('schema', 'ADL SCORM');
        $metadata->addChild('schemaversion', $this->getSchemaVersion());

        return $metadata;
    }

    protected function addOrganizations()
    {
        // Create organizations list node
        $organizations = $this->xml->addChild('organizations');
        $organizations->addAttribute('default', 'default_organization');

        // Create the default organization
        $default = $organizations->addChild('organization');
        $default->addAttribute('identifier', 'default_organization');

        $default->addChild('title', $this->node->getName());

        // Create the Resource item
        $item = $default->addChild('item');
        $item->addAttribute('identifier', 'item_'.$this->node->getId());
        $item->addAttribute('identifierref', 'resource_'.$this->node->getId());
        $item->addAttribute('isvisible', true);

        $item->addChild('title', $this->node->getName());

        return $organizations;
    }

    protected function addResources()
    {
        // Create resources list node
        $resourcesXML = $this->xml->addChild('resources');

        foreach ($this->resources as $resource) {
            $this->addResource($resourcesXML, $resource);
        }
    }

    protected function addResource(\SimpleXMLElement $resourcesXML, array $resource)
    {
        $resourceTemplate = 'scos'.DIRECTORY_SEPARATOR.'resource_'.$resource['node']->getId().'.html';

        $resourceXML = $resourcesXML->addChild('resource');
        $resourceXML->addAttribute('identifier',      'resource_'.$resource['node']->getId());
        $resourceXML->addAttribute('type',            'webcontent');
        $resourceXML->addAttribute('adlcp:scormType', 'sco');
        $resourceXML->addAttribute('href',            $resourceTemplate);

        // Add resource template
        $resourceXML
            ->addChild('file')
            ->addAttribute('href', $resourceTemplate);

        // Add assets
        if (!empty($resource['assets']) && is_array($resource['assets'])) {
            $files = array_keys($resource['assets']);
            foreach ($files as $fileHref) {
                $resourceXML
                    ->addChild('file')
                    ->addAttribute('href', 'assets/'.$fileHref);
            }
        }

        // Add uploaded files
        if (!empty($resource['files']) && is_array($resource['files'])) {
            $files = array_keys($resource['files']);
            foreach ($files as $fileHref) {
                $resourceXML
                    ->addChild('file')
                    ->addAttribute('href', 'files/'.$fileHref);
            }
        }

        // Add translations
        if (!empty($resource['translation_domains']) && is_array($resource['translation_domains'])) {
            foreach ($resource['translation_domains'] as $domain) {
                $resourceXML
                    ->addChild('file')
                    ->addAttribute('href', 'translations/'.$domain.'.js');
            }
        }

        // Add embed resources
        if (!empty($resource['resources']) && is_array($resource['resources'])) {
            foreach ($resource['resources'] as $embedResourceId) {
                $resourceXML
                    ->addChild('dependency')
                    ->addAttribute('idfentifierref', 'resource_'.$embedResourceId);
            }
        }
    }
}

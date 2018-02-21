<?php

namespace Claroline\ScormBundle\Library\Export;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * RichTextExporter.
 *
 * Extracts the list of Resources from an HTML text to add them to the export
 * Replaces claroline URL with SCORM packages URL
 *
 * @DI\Service("claroline.scorm.rich_text_exporter")
 */
class RichTextExporter
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * Class constructor.
     *
     * @param ObjectManager   $om
     * @param ResourceManager $resourceManager
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceManager" = @Di\Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(ObjectManager $om, ResourceManager $resourceManager)
    {
        $this->om = $om;
        $this->resourceManager = $resourceManager;
    }

    /**
     * Parses a rich text to extract the resource list.
     *
     * @param string $text
     * @param bool   $replaceLinks
     *
     * @return array
     */
    public function parse($text, $replaceLinks = true)
    {
        $resources = [];

        // Find media
        $regex = '#[src|href]+="([^"]*file/resource/media/([^\'"]+))"#';

        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $node = $this->resourceManager->getNode($match[2]);
                if ($node) {
                    $resources = $this->storeResource($resources, $node);

                    if ($replaceLinks) {
                        $text = $this->replaceLink($text, $match[1], '../files/file_'.$match[2]);
                    }
                }
            }
        }

        // Find videos (it's a particular case because the url is not same)
        // We also have the Resource id, not the Node one
        $regex = '#[src|href]+="([^"]*video-player/api/video/([^\'"]+)/stream)"#';

        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                // We have the ID of the Resource, not the Node
                $resource = $this->om->getRepository('ClarolineCoreBundle:Resource\File')->find($match[2]);
                if ($resource) {
                    $node = $resource->getResourceNode();
                    $resources = $this->storeResource($resources, $node);

                    if ($replaceLinks) {
                        $text = $this->replaceLink($text, $match[1], '../files/file_'.$node->getId());
                    }
                }
            }
        }

        // Find resources
        $regex = '#[src|href]+="([^"]*resource/open/([^/]+)/([^\'"]+))"#';

        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $node = $this->resourceManager->getNode($match[3]);
                if ($node) {
                    $resources = $this->storeResource($resources, $node);

                    if ($replaceLinks) {
                        $text = $this->replaceLink($text, $match[1], '../scos/resource_'.$match[3].'.html');
                    }
                }
            }
        }

        return [
            'text' => $text,
            'resources' => $resources,
        ];
    }

    private function storeResource(array $resources, ResourceNode $node)
    {
        $resource = $this->resourceManager->getResourceFromNode($node);
        if (empty($resources[$node->getId()])) {
            $resources[$node->getId()] = $resource;
        }

        return $resources;
    }

    private function replaceLink($txt, $oldLink, $newLink)
    {
        $txt = str_replace($oldLink, $newLink, $txt);

        return $txt;
    }
}

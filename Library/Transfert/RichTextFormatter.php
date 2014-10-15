<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;


/**
 * @DI\Service("claroline.importer.rich_text_formatter")
 */
class RichTextFormatter
{
    const REGEX_PLACEHOLDER = '#\[\[uid=([^\]]+)\]\]#';

    private $data;
    private $router;
    private $resourceManagerData;
    private $om;

    /**
     * @DI\InjectParams({
     *     "router"          = @DI\Inject("router"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        UrlGeneratorInterface $router,
        ResourceManager $resourceManager,
        ObjectManager $om
    )
    {
        $data = array();
        $this->resourceManagerData = array();
        $this->resourceManager = $resourceManager;
        $this->router = $router;
        $this->om = $om;
    }

    /**
     * @param $text
     * @param array $resources
     *
     * The $resource array MUST be formatter this way:
     * where the sub array key is an the element uid.
     * array(
     *      'directories' => array(1 => $directory, ...),
     *      'items'       => array(42 => $file, ...)
     * )
     */
    public function format($text)
    {
        preg_match_all(self::REGEX_PLACEHOLDER, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $uid = (int)$match[1];
            $parent = $this->findParentFromDataUid($uid);
            $el = $this->findItemFromUid($uid);
            $node = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(array('parent' => $parent, 'name' => $el['name']));
            $toReplace = $this->generateDisplayedUrlForTinyMce($node);
            $text = str_replace($match[0], $toReplace, $text);
        }

        return $text;
    }

    public function setData(array $data)
    {
        $this->data = $data;

        foreach ($this->data['tools'] as $tool) {
            if ($tool['tool']['type'] = 'resource_manager') {
                $this->resourceManagerData = $tool['tool'];
            }
        }
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    private function findParentFromDataUid($uid)
    {
        //we must find the resource whose uid in the data is $uid
        //this resource already has been persisted before, let's find it !
        //first we find the item in the data
        $itemData = $this->findItemFromUid($uid);
        if ($itemData) return $this->getResourceNodeFromPathData($this->getResourcePathFromItem($itemData));
    }

    private function getResourceNodeFromPathData(array $path)
    {
        //first we find the root
        $node = $this->resourceManager->getWorkspaceRoot($this->workspace);

        foreach ($path as $el)
        {
            $node = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(array('parent' => $node, 'name' => $el['name']));
        }

        return $node;
    }

    private function findItemFromUid($uid)
    {
        foreach ($this->resourceManagerData['data']['items'] as $item) {
            if ($item['item']['uid'] === $uid) return $item['item'];
        }
    }

    private function findDirectoryFromUid($uid)
    {
        foreach ($this->resourceManagerData['data']['directories'] as $item) {
            if ($item['directory']['uid'] === $uid) return $item['directory'];
        }
    }

    private function getResourcePathFromItem(array $item, $path = array())
    {
        $dir = $this->findDirectoryFromUid($item['parent']);

        if ($dir) {
            array_unshift($path, $dir);
            $path = $this->getResourcePathFromItem($dir, $path);
        }

        return $path;
    }

    /**
     * @todo find the method wich generate the url from tinymce
     * @param ResourceNode $node
     */
    private function generateDisplayedUrlForTinyMce(ResourceNode $node)
    {
        $url = $this->router->generate('claro_file_get_media', array('node' => $node->getId()));
        //it may change depeding on the mime type
        $toReplace = "<img style='max-width: 100%;' src='{$url}' alt='{$node->getName()}'>";

        return $toReplace;
    }
} 
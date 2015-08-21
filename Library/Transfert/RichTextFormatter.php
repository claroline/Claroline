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
    //placeholder = [[uid=123]]
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

            if ($node) {
                $toReplace = $this->generateDisplayedUrlForTinyMce($node);
                $text = str_replace($match[0], $toReplace, $text);
            }
        }

        return $text;
    }

    /**
     * For now we only look parse .txt. in the archive.
     * It's way easier that way.
     *
     * @param $_data
     * @param $files
     *
     * @return array
     */
    public function setPlaceHolders(array $files, &$_data)
    {
        $formattedFiles = [];

        foreach ($files as $key => $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $newFile = $file;

            if ($ext === 'txt') {
                $text = $this->setPlaceHolder($file);
                $newFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . 'txt';
                file_put_contents($newFile, $text);
            }

            $formattedFiles[$key] = $newFile;
        }

        return $formattedFiles;
    }

    private function setPlaceHolder($file)
    {
        //urls to be matched...
        //'/file/resource/media/([^']+)#'
        //'/resource/open/([^/]+)/([^']+)'

        $text = file_get_contents($file);
        $baseUrl = $this->router->getContext()->getBaseUrl();

        //first regex
        $regex = '#' . $baseUrl . '/file/resource/media/([^\'"]+)#';

        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $text = $this->replaceLink($text, $match[0], $match[1]);
                //$text = str_replace($match[0], "[[uid=$match[1]]]", $text);
            }
        }

        //second regex
        $regex = '#' . $baseUrl . '/resource/open/([^/]+)/([^\'"]+)#';

        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

        if (count($matches) > 0) {
            foreach ($matches as $match) {
                // I have to change the matches yolo!
                //$text = str_replace($match[0], "[[uid=$match[2]]]", $text);
                $text = $this->replaceLink($text, $match[0], $match[2]);
            }
        }

        return $text;
    }

    private function replaceLink($txt, $fullMatch, $nodeId)
    {
        //videos <source type="video/webm" src=...media...></source>
        //files <a href=...open...> - name - </a>
        //imgs <img style='max-width: 100%;' src='{$url}' alt='{$node->getName()}'>
        $matchReplaced = [];

        preg_match(
            "#(<source|<a|<img)(.*){$fullMatch}(.*)(/>|</a>|</source>)#",
            $txt,
            $matchReplaced
        );

        if (count($matchReplaced)  > 0) {
            $txt = str_replace($matchReplaced[0], "[[uid={$nodeId}]]", $txt);
        }

        return $txt;
    }

    public function setData(array $data)
    {
        $this->data = $data;

        foreach ($this->data['tools'] as $tool) {
            if ($tool['tool']['type'] === 'resource_manager') {
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
        if (isset($this->resourceManagerData['data']['directories'])) {
            foreach ($this->resourceManagerData['data']['directories'] as $item) {
                if ($item['directory']['uid'] === $uid) return $item['directory'];
            }
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
        if (strpos('_' . $node->getMimeType(), 'image') > 0) {
            $url = $this->router->generate('claro_file_get_media', array('node' => $node->getId()));
            return "<img style='max-width: 100%;' src='{$url}' alt='{$node->getName()}'>";
        }

        if (strpos('_' . $node->getMimeType(), 'video') > 0) {
            $url = $this->router->generate('claro_file_get_media', array('node' => $node->getId()));
            return "<source type='{$node->getMimeType()}' src='{$url}'></source>";
        }

        $url = $this->router->generate(
            'claro_resource_open',
            array(
                'resourceType' => $node->getResourceType()->getName(),
                'node' => $node->getId()
            )
        );

        return "<a href='{$url}'>{$node->getName()}</a>";
    }
}

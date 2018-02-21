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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\RichTextFormatEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\TransferManager;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @DI\Service("claroline.importer.rich_text_formatter")
 */
class RichTextFormatter
{
    use LoggableTrait;

    //placeholder = [[uid=123]]
    const REGEX_PLACEHOLDER = '#\[\[(uid=[^\]]+)\]\]#';

    private $data;
    private $router;
    private $resourceManager;
    private $resourceManagerData;
    private $om;
    private $listImporters;
    private $transferManager;
    private $maskManager;
    private $resourceManagerImporter;
    private $eventDispatcher;
    private $config;

    /**
     * RichTextFormatter constructor.
     *
     * @DI\InjectParams({
     *     "router"          = @DI\Inject("router"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "transferManager" = @DI\Inject("claroline.manager.transfer_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "config"          = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param UrlGeneratorInterface        $router
     * @param ResourceManager              $resourceManager
     * @param ObjectManager                $om
     * @param TransferManager              $transferManager
     * @param MaskManager                  $maskManager
     * @param StrictDispatcher             $eventDispatcher
     * @param PlatformConfigurationHandler $config
     */
    public function __construct(
        UrlGeneratorInterface $router,
        ResourceManager $resourceManager,
        ObjectManager $om,
        TransferManager $transferManager,
        MaskManager $maskManager,
        StrictDispatcher $eventDispatcher,
        PlatformConfigurationHandler $config
    ) {
        $this->resourceManagerImporter = null;
        $this->resourceManagerData = [];
        $this->resourceManager = $resourceManager;
        $this->router = $router;
        $this->om = $om;
        $this->listImporters = new ArrayCollection();
        $this->transferManager = $transferManager;
        $this->maskManager = $maskManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->config = $config;
    }

    private function extractFormatOptions($placeholder)
    {
        $output = [];
        //split on comma, ignoring potential commas in quoted text
        foreach (preg_split("#(?!\B['\"][^\"']*),(?![^\"']*['\"]\B)#", $placeholder) as $pair) {
            list($key, $val) = explode('=', trim($pair), 2);
            $output[$key] = trim($val, "'\"");
        }

        return $output;
    }

    /**
     * @param $text
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
            $options = $this->extractFormatOptions($match[1]);
            $uid = $options['uid'];
            // optional parameters
            $option_text = null;
            if (isset($options['text'])) {
                $option_text = $options['text'];
            }
            $option_style = null;
            if (isset($options['style'])) {
                $option_style = $options['style'];
            }
            $option_embed = null;
            if (isset($options['embed'])) {
                $option_embed = (bool) $options['embed'];
            }
            $option_target = null;
            if (isset($options['target'])) {
                $option_target = $options['target'];
            }

            //meh, fix the following lines late
            $parent = $this->findParentFromDataUid($uid);
            $el = $this->findItemFromUid($uid);

            /** @var ResourceNode $node */
            $node = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy([
                    'parent' => $parent,
                    'name' => $el['name'],
                    'resourceType' => $this->resourceManager->getResourceTypeByName($el['type']),
                ]);

            if ($node) {
                $toReplace = $this->generateDisplayedUrlForTinyMce($node, $option_text, $option_embed, $option_style, $option_target);
                $text = str_replace($match[0], $toReplace, $text);
            }
        }
        $event = $this->eventDispatcher->dispatch(
            'rich_text_format_event_import',
            'RichTextFormat',
            [$text]
        );

        return $event->getText();
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

            if ('txt' === $ext) {
                $text = $this->setPlaceHolder($file, $_data, $formattedFiles);
                $newFile = $this->config->getParameter('tmp_dir').DIRECTORY_SEPARATOR.uniqid().'txt';
                file_put_contents($newFile, $text);
            }

            $formattedFiles[$key] = $newFile;
        }

        return $formattedFiles;
    }

    /**
     * If we find an resource id which is a file and not in the export yet, then we
     * export it as well. It's a link towards "something else".
     *
     * URLs to be matched :
     *   - '/file/resource/media/([^']+)#'
     *   - '/resource/open/([^/]+)/([^']+)'
     *   - '/video-player/api/video/([^']+)/stream' (this one is really ugly, see `getOldVideos` for more info)
     *
     * @param string $file
     * @param array  $_data
     * @param array  $_files
     *
     * @return string
     */
    private function setPlaceHolder($file, &$_data, &$_files)
    {
        $text = file_get_contents($file);
        $baseUrl = $this->router->getContext()->getBaseUrl();

        $nodes = []; // nodes referenced into current HTML text

        // Get file resources
        $regex = '#'.$baseUrl.'/file/resource/media/([^\'"]+)#';
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $nodes[$match[0]] = $this->resourceManager->getNode($match[1]);
            }
        }

        // Get other resources
        $regex = '#'.$baseUrl.'/resource/open/([^/]+)/([^\'"]+)#';
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $nodes[$match[0]] = $this->resourceManager->getNode($match[2]);
            }
        }

        // Get videos with old URL format
        $nodes = array_merge($nodes, $this->getOldVideos($text));

        // Replace placeholders for all found nodes
        // Will also import missing ones
        foreach ($nodes as $match => $node) {
            if ($node) {
                // if not yet in export data array, start its import
                $this->includeNodeIfMissing($node, $_data, $_files);
                $text = $this->replaceLink($node, $text, $match);
            }
        }

        /** @var RichTextFormatEvent $event */
        $event = $this->eventDispatcher->dispatch(
            'rich_text_format_event_export',
            'RichTextFormat',
            [$text, &$_data, &$_files]
        );

        return $event->getText();
    }

    /**
     * Get videos from HTML text which uses old URL format.
     *
     * This is for retro compatibility purpose.
     * In last version, video URLs have the same format than other file types.
     *
     * Old format :
     *   '/BASE_PATH/video-player/api/video/([^']+)/stream'
     *
     * Problems / differences with other resources :
     *   - main format pattern is not the same has other files
     *   - the ID in the URL is not the ResourceNode ID
     *   - the URL does not contain scheme and host
     *
     * @param string $text
     *
     * @return array
     */
    private function getOldVideos($text)
    {
        $nodes = [];

        $basePath = str_replace(
            $this->router->getContext()->getScheme().'://'.$this->router->getContext()->getHost(),
            '',
            $this->router->getContext()->getBaseUrl()
        );

        $regex = '#'.$basePath.'/video-player/api/video/([^/]+)/stream#';
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        if (count($matches) > 0) {
            foreach ($matches as $match) {
                $video = $this->om->getRepository('ClarolineCoreBundle:Resource\File')->find($match[1]);
                if ($video) {
                    $nodes[$match[0]] = $video->getResourceNode();
                }
            }
        }

        return $nodes;
    }

    private function includeNodeIfMissing(ResourceNode $node, &$_data, &$_files)
    {
        if (!$this->getItemFromUid($node->getId(), $_data)) {
            $this->createDataFolder($_data);

            $el = $this->getImporterByName('resource_manager')->getResourceElement(
                $node,
                $node->getWorkspace(),
                $_files,
                $_data,
                true
            );
            $el['item']['parent'] = 'data_folder';
            $el['item']['roles'] = [['role' => [
                'name' => 'ROLE_USER',
                'rights' => $this->maskManager->decodeMask(7, $node->getResourceType()),
            ]]];

            if (!$this->getItemFromUid($el['item']['uid'], $_data)) {
                $_data['data']['items'][] = $el;
            }
        }
    }

    private function replaceLink(ResourceNode $node, $txt, $fullMatch)
    {
        //videos <source type="video/webm" src=...media...></source>
        //files <a href=...open...> - name - </a>
        //imgs <img style='max-width: 100%;' src='{$url}' alt='{$node->getName()}'>
        $matchReplaced = [];
        $fullMatch = preg_quote($fullMatch);

        //match hyperlink and extract text
        preg_match(
            "#<a.*{$fullMatch}[^>]+>(.*)<\/a>#i",
            $txt,
            $matchReplaced
        );
        if (count($matchReplaced) > 0) {
            //custom hyperlink text, if different from node name
            $text_option = '';
            if (isset($matchReplaced[1]) && $matchReplaced[1] !== $node->getName()) {
                $text_option = ",text='".addslashes(strip_tags($matchReplaced[1]))."'";
            }
            //css style option
            $css_style_option = '';
            $css_style = $this->extractCssStyle($matchReplaced[0]);
            if (isset($css_style)) {
                $css_style_option = ",style='".addslashes($css_style)."'";
            }
            //target option
            $target_option = '';
            $target = $this->extractTarget($matchReplaced[0]);
            if (isset($target)) {
                $target_option = ",target='".$target."'";
            }
            //simple hyperlink, no embed, option only necessary for medias
            $embed_option = '';
            if (strpos('_'.$node->getMimeType(), 'image') > 0 || strpos('_'.$node->getMimeType(), 'video') > 0 || strpos('_'.$node->getMimeType(), 'audio') > 0) {
                $embed_option = ',embed=0';
            }

            $tag = '[[uid='.$node->getGuid().$text_option.$embed_option.$css_style_option.$target_option.']]';
            $txt = str_replace($matchReplaced[0], $tag, $txt);
        } else {
            //match embeded media
            preg_match(
                "#<(source|img).*{$fullMatch}[^>]+>(<\/source>)?#i",
                $txt,
                $matchReplaced
            );
            if (count($matchReplaced) > 0) {
                //css style option
                $css_style_option = '';
                $css_style = $this->extractCssStyle($matchReplaced[0]);
                if (isset($css_style)) {
                    $css_style_option = ",style='".addslashes($css_style)."'";
                }
                $tag = '[[uid='.$node->getGuid().$css_style_option.']]';
                $txt = str_replace($matchReplaced[0], $tag, $txt);
            }
        }

        return $txt;
    }

    private function extractCssStyle($txt)
    {
        preg_match(
            "#style=[\"\']([^\"\']+)[\"\']#i",
            $txt,
            $match
        );
        if (count($match) > 0) {
            return $match[1];
        }

        return null;
    }

    private function extractTarget($txt)
    {
        preg_match(
            "#target=[\"\']([^\"\']+)[\"\']#i",
            $txt,
            $match
        );
        if (count($match) > 0) {
            return $match[1];
        }

        return null;
    }

    /**
     * @todo remove this for claroline v6
     */
    public function setData(array $data)
    {
        $this->data = $data;

        foreach ($this->data['tools'] as $tool) {
            if ($tool['tool']['type'] === 'resource_manager') {
                $this->resourceManagerData = $tool['tool'];
            }
        }
    }

    /**
     * @todo remove this for claroline v6
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function findParentFromDataUid($uid)
    {
        //we must find the resource whose uid in the data is $uid
        //this resource already has been persisted before, let's find it !
        //first we find the item in the data
        $itemData = $this->findItemFromUid($uid);
        if ($itemData) {
            return $this->getResourceNodeFromPathData($this->getResourcePathFromItem($itemData));
        }
    }

    public function getResourceNodeFromPathData(array $path)
    {
        //first we find the root
        $node = $this->resourceManager->getWorkspaceRoot($this->workspace);

        foreach ($path as $el) {
            $node = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(['parent' => $node, 'name' => $el['name']]);
        }

        return $node;
    }

    /**
     * @todo remove this for claroline v6
     * use getItemFromUid($uid, $resManagerData) instead
     */
    public function findItemFromUid($uid)
    {
        if (isset($this->resourceManagerData['data']['items'])) {
            foreach ($this->resourceManagerData['data']['items'] as $item) {
                //without cast, comparaison would fail in some instances (uid is either int or string, depending)
                if ((string) ($item['item']['uid']) === (string) $uid) {
                    return $item['item'];
                }
            }
        }
    }

    public function getItemFromUid($uid, $resManagerData)
    {
        if (isset($resManagerData['data']['items'])) {
            foreach ($resManagerData['data']['items'] as $item) {
                //without cast, comparaison would fail in some instances (uid is either int or string, depending)
                if ((string) ($item['item']['uid']) === (string) $uid) {
                    return $item['item'];
                }
            }
        }
    }

    /**
     * @todo remove this for claroline v6
     * use getDirectoryFromUid($uid, $resManagerData) instead
     */
    public function findDirectoryFromUid($uid)
    {
        if (isset($this->resourceManagerData['data']['directories'])) {
            foreach ($this->resourceManagerData['data']['directories'] as $item) {
                //without cast, comparaison would fail in some instances (uid is either int or string, depending)
                if ((string) ($item['directory']['uid']) === (string) $uid) {
                    return $item['directory'];
                }
            }
        }
    }

    public function getDirectoryFromUid($uid, $resManagerData)
    {
        if (isset($resManagerData['data']['directories'])) {
            foreach ($resManagerData['data']['directories'] as $item) {
                //without cast, comparaison would fail in some instances (uid is either int or string, depending)
                if ((string) ($item['directory']['uid']) === (string) $uid) {
                    return $item['directory'];
                }
            }
        }
    }

    public function getResourcePathFromItem(array $item, $path = [])
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
     *
     * @param ResourceNode $node
     */
    public function generateDisplayedUrlForTinyMce(ResourceNode $node, $text, $embed, $style = null, $target = null)
    {
        $cssStyle = isset($style) ? "style='".stripslashes($style)."'" : '';

        if (strpos('_'.$node->getMimeType(), 'image') > 0) {
            $url = $this->router->generate(
                'claro_file_get_media',
                ['node' => $node->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            //embed images by default
            if (!isset($embed) || $embed) {
                if (empty($cssStyle)) {
                    $cssStyle = "style='max-width:100%;'";
                }

                return '<img '.$cssStyle." src='".$url."' alt='".$node->getName()."'>";
            }
        }

        if (strpos('_'.$node->getMimeType(), 'video') > 0 || strpos('_'.$node->getMimeType(), 'audio') > 0) {
            $url = $this->router->generate(
                'claro_file_get_media',
                ['node' => $node->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //embed audio/video by default
            if (!isset($embed) || $embed) {
                return "<source type='".$node->getMimeType()."' src='".$url."'></source>";
            }
        }

        if (strpos('_'.$node->getMimeType(), 'x-shockwave-flash') > 0) {
            $url = $this->router->generate(
                'claro_file_get_media',
                ['node' => $node->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //embed flash by default
            if (!isset($embed) || $embed) {
                return '<object '.$cssStyle." type='".$node->getMimeType()."' data='".$url."'>".
                            "<param name='allowFullScreen' value='true' />".
                            "<param name='src' value='".$url."' />".
                        '</object>';
            }
        }

        //hyperlink text, fallback to node name if none
        $link_text = isset($text) ? stripslashes($text) : $node->getName();
        $targetProperty = isset($target) ? "target='".$target."'" : '';

        //no embed by default
        if ($embed) {
            $url = $this->router->generate(
                'claro_resource_open',
                [
                    'resourceType' => $node->getResourceType()->getName(),
                    'node' => $node->getId(),
                    'iframe' => 1,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return "<iframe {$cssStyle} src='{$url}'></iframe>";
        } else {
            $url = $this->router->generate(
                'claro_resource_open',
                [
                    'resourceType' => $node->getResourceType()->getName(),
                    'node' => $node->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return "<a {$cssStyle} {$targetProperty} href='{$url}'>{$link_text}</a>";
        }
    }

    public function addImporter(Importer $importer)
    {
        return $this->listImporters->add($importer);
    }

    public function getImporterByName($name)
    {
        foreach ($this->listImporters as $importer) {
            if ($importer->getName() === $name) {
                return $importer;
            }
        }

        return;
    }

    public function createDataFolder(array &$_data)
    {
        if ($this->dataFolderExists($_data)) {
            return;
        }

        $roles = [];
        $roles[] = ['role' => [
            'name' => 'ROLE_USER',
            'rights' => $this->maskManager->decodeMask(7, $this->resourceManager->getResourceTypeByName('directory')),
        ]];

        //if root doesn't exist, we create it
        if (!isset($_data['data']['root'])) {
            $root = [
              'uid' => 'root',
              'role' => [
                'name' => 'ROLE_WS_COLLABORATOR',
                'rights' => [
                  'open' => true,
                  'copy' => false,
                  'export' => true,
                  'delete' => false,
                  'edit' => false,
                  'administrate' => false,
                ],
              ],
            ];

            $_data['data']['root'] = $root;
        }

        $parentId = $_data['data']['root']['uid'];

        $_data['data']['directories'][] = ['directory' => [
            'name' => 'data_folder',
            'creator' => null,
            'parent' => $parentId,
            'published' => true,
            'uid' => 'data_folder',
            'roles' => $roles,
            'index' => null,
        ]];
    }

    private function dataFolderExists($data)
    {
        if (!isset($data['data']['directories'])) {
            return false;
        }
        foreach ($data['data']['directories'] as $directory) {
            if ($directory['directory']['uid'] === 'data_folder') {
                return true;
            }
        }

        return false;
    }
}

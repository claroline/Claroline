<?php

namespace Claroline\CoreBundle\Listener\Transfer;

use Claroline\CoreBundle\Event\RichTextFormatEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Routing\RouterInterface;
use Claroline\CoreBundle\Library\Transfert\RichTextFormatter;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * @DI\Service()
 */
class ImgFormatListener
{
    const REGEX_PLACEHOLDER = '#\[\[img=([^\]]+)\]\]#';

    private $router;
    private $om;
    private $formatter;
    private $resourceManager;
    private $maskManager;

    /**
     * @DI\InjectParams({
     *     "router"          = @DI\Inject("router"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "formatter"       = @DI\Inject("claroline.importer.rich_text_formatter"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager")
     * })
     */
    public function __construct(
        RouterInterface $router,
        ObjectManager $om,
        RichTextFormatter $formatter,
        ResourceManager $resourceManager,
        MaskManager $maskManager
    )
    {
        $this->router = $router;
        $this->om = $om;
        $this->formatter = $formatter;
        $this->resourceManager = $resourceManager;
        $this->maskManager = $maskManager;
    }

    /**
     * @DI\Observe("rich_text_format_event_export")
     *
     * @param RichTextFormatEvent $event
     * This is pretty much the same as the RichTextFormatter one
     */
    public function export(RichTextFormatEvent $event)
    {
        //urls to be matched...
        //'/file/resource/media/([^']+)#'
        //'/resource/open/([^/]+)/([^']+)'
        $text = $event->getText();
        $baseUrl = $this->router->getContext()->getBaseUrl();
        $_data = $event->getData();
        $_files = $event->getFiles();

        //first regex
        $regex = '#' . $baseUrl . '/file/resource/media/([^\'"]+)#';

        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

        if (count($matches) > 0) {
            foreach ($matches as $match) {

                if (!$this->formatter->getItemFromUid($match[1], $_data)) {
                    $this->formatter->createDataFolder($_data);
                    $node = $this->resourceManager->getNode($match[1]);

                    if ($node && $node->getResourceType()->getName() === 'file') {
                        $el = $this->formatter->getImporterByName('resource_manager')->getResourceElement(
                            $node,
                            $node->getWorkspace(),
                            $_files,
                            $_data,
                            true
                        );
                        $el['item']['parent'] = 'data_folder';
                        $el['item']['roles'] = array(array('role' => array(
                            'name'   => 'ROLE_USER',
                            'rights' => $this->maskManager->decodeMask(7, $this->resourceManager->getResourceTypeByName('file'))
                        )));
                        $_data['data']['items'][] = $el;
                    }
                }

                $text = $this->replaceLink($text, $match[0], $match[1]);
            }
        }

        $event->setText($text);
    }

    /**
     * @DI\Observe("rich_text_format_event_import")
     *
     * @param RichTextFormatEvent $event
     */
    public function import(RichTextFormatEvent $event)
    {
        $text = $event->getText();
        preg_match_all(self::REGEX_PLACEHOLDER, $text, $matches, PREG_SET_ORDER);
        $baseUrl = $this->router->getContext()->getBaseUrl();

        foreach ($matches as $match) {
            $imgdata = explode('@', $match[1]);
            $uid = (int)$imgdata[0]; //not really actually ~that would be the part before the first (@)
            $parent = $this->formatter->findParentFromDataUid($uid);
            $el = $this->formatter->findItemFromUid($uid);
            $node = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(array('parent' => $parent, 'name' => $el['name']));

            if ($node) {
                $toReplace = $this->generateDisplayedUrlForTinyMce($node, $match);
                $text = str_replace($match[0], $toReplace, $text);
            }
        }

        $event->setText($text);
    }

    private function replaceLink($txt, $fullMatch, $nodeId)
    {
        $width = $heigth = $style = null;

        preg_match(
            "#(<img)(.*){$fullMatch}(.*)(/>)#",
            $txt,
            $matchReplaced
        );

        if (count($matchReplaced)  > 0) {
            $el = $matchReplaced[0];
            //grep the width
            preg_match('#(.*)width="([^"]+)(.*)#', $el, $widths);
            $width = $widths[2];
            //grep the heigth
            preg_match('#(.*)height="([^"]+)(.*)#', $el, $heights);
            $height = $heights[2];
            //grep the style
            preg_match('#(.*)style="([^"]+)(.*)#', $el, $styles);
            $style = $styles[2];

            $txt = str_replace($el, "[[img={$nodeId}@{$width}@{$height}@{$style}]]", $txt);
        }

        return $txt;
    }

    /**
     * @todo find the method wich generate the url from tinymce
     * @param ResourceNode $node
     */
    public function generateDisplayedUrlForTinyMce(ResourceNode $node, $match)
    {
        $imgdata = explode('@', $match[1]);
        $width = $imgdata[1];
        $height = $imgdata[2];
        $style = $imgdata[3];
        $url = $this->router->generate('claro_file_get_media', array('node' => $node->getId()));
        $img = "<img ";

        if ($width !== '') $img .= "width='{$width}' ";
        if ($height !== '') $img .= "height='{$height}' ";
        if ($style !== '') $img .= "style='{$style}' ";

        $img .= "src='{$url}' alt='{$node->getName()}'>";

        return $img;
    }
}

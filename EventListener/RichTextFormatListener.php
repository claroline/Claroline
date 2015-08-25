<?php

namespace Innova\PathBundle\EventListener;

use Claroline\CoreBundle\Event\RichTextFormatEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Routing\RouterInterface;
use Claroline\CoreBundle\Library\Transfert\RichTextFormatter;

/**
 * @DI\Service()
 */
class RichTextFormatListener
{
    const REGEX_PLACEHOLDER = '#\[\[path_node_id=([^\]]+)\]\]#';

    private $router;
    private $om;
    private $formatter;
    private $resourceManager;

    /**
     * @DI\InjectParams({
     *     "router"          = @DI\Inject("router"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "formatter"       = @DI\Inject("claroline.importer.rich_text_formatter"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(
        RouterInterface $router,
        ObjectManager $om,
        RichTextFormatter $formatter,
        ResourceManager $resourceManager
    )
    {
        $this->router = $router;
        $this->om = $om;
        $this->formatter = $formatter;
        $this->resourceManager = $resourceManager;
    }

    /**
     * @DI\Observe("rich_text_format_event_export")
     *
     * @param RichTextFormatEvent $event
     */
    public function export(RichTextFormatEvent $event)
    {
        $text = $event->getText();
        $baseUrl = $this->router->getContext()->getBaseUrl();
        //innova path is a plugin but it's an important one...
        $regex = '#' . $baseUrl . '/innova_path/player/([^\#]+)([^\'"]+)#';
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

        if (count($matches) > 0) {
            foreach ($matches as $match) {
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
            $uid = (int)$match[1];
            $parent = $this->formatter->findParentFromDataUid($uid);
            $el = $this->formatter->findItemFromUid($uid);
            $node = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(array('parent' => $parent, 'name' => $el['name']));

            if ($node) {
                $resource = $this->resourceManager->getResourceFromNode($node);
                $toReplace = "<a href='{$baseUrl}/innova_path/player/{$resource->getId()}#'>{$node->getName()}</a>";
                $text = str_replace($match[0], $toReplace, $text);
                $event->setText($text);
            }
        }
    }

    private function replaceLink($txt, $fullMatch, $pathId)
    {
        $nodeId = $this->om
            ->getRepository('Innova\PathBundle\Entity\Path\Path')
            ->find($pathId)
            ->getResourceNode()
            ->getId();

        $matchReplaced = [];
        $fullMatch = str_replace('#', '\\#', $fullMatch);

        preg_match(
            "#(<a)(.*){$fullMatch}(.*)(</a>)#",
            $txt,
            $matchReplaced
        );

        if (count($matchReplaced)  > 0) {
            $txt = str_replace($matchReplaced[0], "[[path_node_id={$nodeId}]]", $txt);
        }

        return $txt;
    }
}

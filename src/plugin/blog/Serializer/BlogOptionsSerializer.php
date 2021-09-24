<?php

namespace Icap\BlogBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Manager\BlogManager;

class BlogOptionsSerializer
{
    use SerializerTrait;

    private $blogManager;

    /**
     * BlogOptions serializer constructor.
     */
    public function __construct(BlogManager $blogManager)
    {
        $this->blogManager = $blogManager;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Icap\BlogBundle\Entity\BlogOptions';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/blog/options.json';
    }

    public function getName()
    {
        return 'blog_options';
    }

    public function serialize(Blog $blog, BlogOptions $blogOptions, array $options = []): array
    {
        return [
            'authorizeComment' => $blogOptions->getAuthorizeComment(),
            'authorizeAnonymousComment' => $blogOptions->getAuthorizeAnonymousComment(),
            'postPerPage' => $blogOptions->getPostPerPage(),
            'autoPublishPost' => $blogOptions->getAutoPublishPost(),
            'commentModerationMode' => $this->getModerationModeStringValue($blogOptions->getCommentModerationMode()),
            'displayPostViewCounter' => $blogOptions->getDisplayPostViewCounter(),
            'tagCloud' => null !== $blogOptions->getTagCloud() ? $this->getTagModeStringValue($blogOptions->getTagCloud()) : '0',
            'widgetOrder' => $this->serializeWidgetOrder($blogOptions->getListWidgetBlog()),
            'widgetList' => $this->serializeWidgetList(),
            'tagTopMode' => $blogOptions->isTagTopMode(),
            'maxTag' => $blogOptions->getMaxTag(),
            'displayFullPosts' => $blogOptions->getDisplayFullPosts(),
            'infos' => $blog->getInfos(),
        ];
    }

    private function getModerationModeStringValue($value)
    {
        switch ($value) {
            case 0:
                $strVal = 'never';
                break;
            case 1:
                $strVal = 'first';
                break;
            case 2:
                $strVal = 'always';
                break;
            default:
                $strVal = 'never';
        }

        return $strVal;
    }

    private function getModerationModeIntValue($value)
    {
        switch ($value) {
            case 'never':
                $intVal = 0;
                break;
            case 'first':
                $intVal = 1;
                break;
            case 'always':
                $intVal = 2;
                break;
            default:
                $intVal = 0;
        }

        return $intVal;
    }

    private function getTagModeStringValue($value)
    {
        switch ($value) {
            case 0:
                $strVal = 'classic';
                break;
            case 2:
                $strVal = 'classic_number';
                break;
            case 3:
                $strVal = 'vertical';
                break;
            default:
                //sphere3d (1) deprecated, fallback on classic
                $strVal = 'classic';
        }

        return $strVal;
    }

    private function getTagModeIntValue($value)
    {
        switch ($value) {
            case 'classic':
                $intVal = 0;
                break;
            case 'classic_number':
                $intVal = 2;
                break;
            case 'vertical':
                $intVal = 3;
                break;
            default:
                //sphere3d (1) deprecated, fallback on classic
                $intVal = 0;
        }

        return $intVal;
    }

    private function serializeWidgetList()
    {
        $panelInfo = $this->blogManager->getPanelInfos();
        $panels = [];
        $i = 1;
        foreach ($panelInfo as $panel) {
            $panels[] = [
                'id' => $i,
                'nameTemplate' => $panel,
            ];
            ++$i;
        }

        return $panels;
    }

    private function serializeWidgetOrder($mask, array $options = [])
    {
        $panelInfo = $this->blogManager->getPanelInfos();
        $panelOldInfo = $this->blogManager->getOldPanelInfos();
        $orderPanelsTable = [];
        $maskLength = strlen($mask);
        for ($maskPosition = 0, $entreTableau = 0; $maskPosition < $maskLength; $maskPosition += 2, $entreTableau++) {
            $i = $mask[$maskPosition];
            if (in_array($panelOldInfo[$i], $panelInfo)) {
                $orderPanelsTable[] = [
                    'nameTemplate' => $panelOldInfo[$i],
                    'visibility' => (bool) $mask[$maskPosition + 1],
                    'id' => (int) $mask[$maskPosition],
                ];
            }
        }

        return $orderPanelsTable;
    }

    private function deserializeWidgetOrder($orderPanelsTable, array $options = [])
    {
        $mask = null;
        foreach ($orderPanelsTable as $row) {
            $mask = $mask.$row['id'].(int) ($row['visibility']);
        }

        return $mask;
    }

    public function deserialize(array $data, BlogOptions $blogOptions = null, array $options = []): BlogOptions
    {
        if (empty($blogOptions)) {
            $blogOptions = new BlogOptions();
        }

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $blogOptions);
        }

        $this->sipe('authorizeComment', 'setAuthorizeComment', $data, $blogOptions);
        $this->sipe('authorizeAnonymousComment', 'setAuthorizeAnonymousComment', $data, $blogOptions);
        $this->sipe('postPerPage', 'setPostPerPage', $data, $blogOptions);
        $this->sipe('autoPublishPost', 'setAutoPublishPost', $data, $blogOptions);
        $this->sipe('displayPostViewCounter', 'setDisplayPostViewCounter', $data, $blogOptions);
        $this->sipe('tagTopMode', 'setTagTopMode', $data, $blogOptions);
        $this->sipe('maxTag', 'setMaxTag', $data, $blogOptions);
        $this->sipe('displayFullPosts', 'setDisplayFullPosts', $data, $blogOptions);

        if (isset($data['commentModerationMode'])) {
            $blogOptions->setCommentModerationMode($this->getModerationModeIntValue($data['commentModerationMode']));
        }
        if (isset($data['tagCloud'])) {
            $blogOptions->setTagCloud($this->getTagModeIntValue($data['tagCloud']));
        }
        if (isset($data['widgetOrder'])) {
            $blogOptions->setListWidgetBlog($this->deserializeWidgetOrder($data['widgetOrder']));
        }

        return $blogOptions;
    }
}

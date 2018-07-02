<?php

namespace Icap\BlogBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Manager\BlogManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.blog.options")
 * @DI\Tag("claroline.serializer")
 */
class BlogOptionsSerializer
{
    use SerializerTrait;

    private $blogManager;

    /**
     * BlogOptions serializer constructor.
     *
     * @DI\InjectParams({
     *     "blogManager" = @DI\Inject("icap_blog.manager.blog")
     * })
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

    /**
     * @param Blog        $blog
     * @param BlogOptions $options
     * @param array       $options
     *
     * @return array - The serialized representation of blog options
     */
    public function serialize(Blog $blog, BlogOptions $blogOptions, array $options = [])
    {
        return [
            'authorizeComment' => $blogOptions->getAuthorizeComment(),
            'authorizeAnonymousComment' => $blogOptions->getAuthorizeAnonymousComment(),
            'postPerPage' => $blogOptions->getPostPerPage(),
            'autoPublishPost' => $blogOptions->getAutoPublishPost(),
            'commentModerationMode' => strval($blogOptions->getCommentModerationMode()),
            'displayTitle' => $blogOptions->getDisplayTitle(),
            'bannerActivate' => $blogOptions->isBannerActivate(),
            'displayPostViewCounter' => $blogOptions->getDisplayPostViewCounter(),
            'tagCloud' => null !== $blogOptions->getTagCloud() ? strval($blogOptions->getTagCloud()) : '0',
            'widgetOrder' => $this->serializeWidgetOrder($blogOptions->getListWidgetBlog()),
            'widgetList' => $this->serializeWidgetList($this->blogManager->getPanelInfos()),
            'tagTopMode' => $blogOptions->isTagTopMode(),
            'maxTag' => $blogOptions->getMaxTag(),
            'displayFullPosts' => $blogOptions->getDisplayFullPosts(),
            'infos' => $blog->getInfos(),
        ];
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
        for ($maskPosition = 0, $entreTableau = 0; $maskPosition < strlen($mask); $maskPosition += 2, $entreTableau++) {
            $i = $mask[$maskPosition];
            if (in_array($panelOldInfo[$i], $panelInfo)) {
                $orderPanelsTable[] = [
                    'nameTemplate' => $panelOldInfo[$i],
                    'visibility' => (int) $mask[$maskPosition + 1],
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
            $mask = $mask.$row['id'].$row['visibility'];
        }

        return $mask;
    }

    /**
     * @param array              $data
     * @param BlogOptions | null $options
     * @param array              $options
     *
     * @return BlogOptions - The deserialized blogOptions entity
     */
    public function deserialize($data, BlogOptions $blogOptions = null, array $options = [])
    {
        if (empty($blogOptions)) {
            $blogOptions = new BlogOptions();
        }

        $this->sipe('id', 'setUuid', $data, $blogOptions);
        $blogOptions->setAuthorizeComment($data['authorizeComment']);
        $blogOptions->setAuthorizeAnonymousComment($data['authorizeAnonymousComment']);
        $blogOptions->setPostPerPage($data['postPerPage']);
        $blogOptions->setAutoPublishPost($data['autoPublishPost']);
        $blogOptions->setCommentModerationMode($data['commentModerationMode']);
        $blogOptions->setDisplayTitle($data['displayTitle']);
        $blogOptions->setBannerActivate($data['bannerActivate']);
        $blogOptions->setDisplayPostViewCounter($data['displayPostViewCounter']);
        $blogOptions->setTagCloud($data['tagCloud']);
        $blogOptions->setListWidgetBlog($this->deserializeWidgetOrder($data['widgetOrder']));
        $blogOptions->setTagTopMode($data['tagTopMode']);
        $blogOptions->setMaxTag($data['maxTag']);
        $blogOptions->setDisplayFullPosts($data['displayFullPosts']);

        return $blogOptions;
    }
}

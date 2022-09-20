<?php

namespace Icap\BlogBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BlogManager
{
    private $objectManager;
    private $repo;
    private $eventDispatcher;
    private $postManager;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        PostManager $postManager
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->postManager = $postManager;

        $this->repo = $this->objectManager->getRepository(Blog::class);
    }

    public function getPanelInfos()
    {
        return [
            'infobar',
            'rss',
            'tagcloud',
            'redactor',
            'calendar',
            'archives',
        ];
    }

    public function getOldPanelInfos()
    {
        return [
            'search',
            'infobar',
            'rss',
            'tagcloud',
            'redactor',
            'calendar',
            'archives',
        ];
    }

    public function updateOptions(Blog $blog, BlogOptions $options, $infos)
    {
        $currentOptions = $blog->getOptions();
        $currentOptions->setAuthorizeComment($options->getAuthorizeComment());
        $currentOptions->setAuthorizeAnonymousComment($options->getAuthorizeAnonymousComment());
        $currentOptions->setPostPerPage($options->getPostPerPage());
        $currentOptions->setAutoPublishPost($options->getAutoPublishPost());
        $currentOptions->setAutoPublishComment($options->getAutoPublishComment());
        $currentOptions->setDisplayPostViewCounter($options->getDisplayPostViewCounter());
        $currentOptions->setBannerBackgroundColor($options->getBannerBackgroundColor());
        $currentOptions->setBannerHeight($options->getBannerHeight());
        $currentOptions->setBannerBackgroundImagePosition($options->getBannerBackgroundImagePosition());
        $currentOptions->setBannerBackgroundImageRepeat($options->getBannerBackgroundImageRepeat());
        $currentOptions->setTagCloud($options->getTagCloud());
        $currentOptions->setListWidgetBlog($options->getListWidgetBlog());
        $currentOptions->setTagTopMode($options->isTagTopMode());
        $currentOptions->setMaxTag($options->getMaxTag());
        $currentOptions->setCommentModerationMode($options->getCommentModerationMode());
        $currentOptions->setDisplayFullPosts($options->getDisplayFullPosts());

        $blog->setInfos($infos);

        $this->objectManager->flush();

        return $this->objectManager->getUnitOfWork();
    }

    /**
     * Get tags used in the blog.
     *
     * @param Blog  $blog
     * @param array $posts
     *
     * @return array
     */
    public function getTags($blog, array $postData = [])
    {
        $postUuids = array_column($postData, 'id');

        $event = new GenericDataEvent([
            'class' => 'Icap\BlogBundle\Entity\Post',
            'ids' => $postUuids,
            'frequency' => true,
        ]);

        $this->eventDispatcher->dispatch(
            $event,
            'claroline_retrieve_used_tags_by_class_and_ids'
        );
        $tags = $event->getResponse();

        //only keep max tag number, if defined
        if ($blog->getOptions()->isTagTopMode() && $blog->getOptions()->getMaxTag() > 0) {
            arsort($tags);
            $tags = array_slice($tags, 0, $blog->getOptions()->getMaxTag());
        }

        return (object) $tags;
    }
}

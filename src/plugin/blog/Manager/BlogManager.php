<?php

namespace Icap\BlogBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Member;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BlogManager
{
    private $objectManager;
    private $repo;
    private $memberRepo;
    private $eventDispatcher;
    private $postManager;
    private $fileUtils;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        PostManager $postManager,
        FileUtilities $fileUtils)
    {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->postManager = $postManager;
        $this->fileUtils = $fileUtils;

        $this->repo = $this->objectManager->getRepository(Blog::class);
        $this->memberRepo = $this->objectManager->getRepository(Member::class);
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
        $currentOptions->setDisplayTitle($options->getDisplayTitle());
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

    /**
     * Find all member for a given user and the replace him by another.
     */
    public function replaceMemberAuthor(User $from, User $to)
    {
        $fromIsMember = false;
        $froms = $this->memberRepo->findByUser($from);
        if (count($froms) > 0) {
            $fromIsMember = true;
        }

        $toIsMember = false;
        $tos = $this->memberRepo->findByUser($to);
        if (count($tos) > 0) {
            $toIsMember = true;
        }

        if ($toIsMember && $fromIsMember) {
            //user kept already have its member entry, delete the old one
            foreach ($froms as $member) {
                $this->objectManager->remove($member);
            }
            $this->objectManager->flush();
        } elseif (!$toIsMember && $fromIsMember) {
            //update entry for kept user
            foreach ($froms as $member) {
                $member->setUser($to);
            }
            $this->objectManager->flush();
        }
    }
}

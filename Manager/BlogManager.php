<?php

namespace Icap\BlogBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Icap\BlogBundle\Entity\Blog;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_blog.manager.blog")
 */
class BlogManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @DI\InjectParams({
     *      "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Workspace $workspace
     * @param array     $files
     * @param Blog      $object
     *
     * @return array
     */
    public function exportBlog(Workspace $workspace, array &$files, Blog $object)
    {
        $data = [];

        $data['options'] = [
            'authorize_comment'                 => $object->getOptions()->getAuthorizeComment(),
            'authorize_anonymous_comment'       => $object->getOptions()->getAuthorizeAnonymousComment(),
            'post_per_page'                     => $object->getOptions()->getPostPerPage(),
            'auto_publish_post'                 => $object->getOptions()->getAutoPublishPost(),
            'auto_publish_comment'              => $object->getOptions()->getAutoPublishComment(),
            'display_title'                     => $object->getOptions()->getDisplayTitle(),
            'banner_activate'                   => $object->getOptions()->isBannerActivate(),
            'display_post_view_counter'         => $object->getOptions()->getDisplayPostViewCounter(),
            'banner_background_color'           => $object->getOptions()->getBannerBackgroundColor(),
            'banner_height'                     => $object->getOptions()->getBannerHeight(),
            'banner_background_image'           => $object->getOptions()->getBannerBackgroundImage(),
            'banner_background_image_position'  => $object->getOptions()->getBannerBackgroundImagePosition(),
            'banner_background_image_repeat'    => $object->getOptions()->getBannerBackgroundImageRepeat(),
            'tag_cloud'                         => $object->getOptions()->getTagCloud()
        ];

        $data['posts'] = [];

        foreach ($object->getPosts() as $post) {
            $postUid = uniqid() . '.txt';
            $postTemporaryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $postUid;
            file_put_contents($postTemporaryPath, $post->getContent());
            $files[$postUid] = $postTemporaryPath;

            $tags = [];

            foreach ($post->getTags() as $tag) {
                $tags[] = [
                    'name' => $tag->getName()
                ];
            }

            $comments = [];

            foreach ($post->getComments() as $comment) {
                $commentUid = uniqid() . '.txt';
                $commentTemporaryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $commentUid;
                file_put_contents($commentTemporaryPath, $comment->getMessage());
                $files[$commentUid] = $commentTemporaryPath;

                $comments[] = [
                    'message' => $commentUid,
                    'author'  => $comment->getAuthor()->getMail()
                ];
            }

            $postArray = [
                'title'    => $post->getTitle(),
                'content'  => $postUid,
                'author'   => $post->getAuthor()->getMail(),
                'status'   => $post->getStatus(),
                'tags'     => $tags,
                'comments' => $comments
            ];

            $data['posts'][] = $postArray;
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return Blog
     */
    public function importBlog(array $data)
    {
        $blog = new Blog();

        return $blog;
    }
}

<?php

namespace Icap\BlogBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Tag;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     *      "uploadDir" = @DI\Inject("%icap.blog.banner_directory%")
     * })
     */
    public function __construct(ObjectManager $objectManager, $uploadDir)
    {
        $this->objectManager = $objectManager;
        $this->uploadDir = $uploadDir;
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
            'authorize_comment' => $object->getOptions()->getAuthorizeComment(),
            'authorize_anonymous_comment' => $object->getOptions()->getAuthorizeAnonymousComment(),
            'post_per_page' => $object->getOptions()->getPostPerPage(),
            'auto_publish_post' => $object->getOptions()->getAutoPublishPost(),
            'auto_publish_comment' => $object->getOptions()->getAutoPublishComment(),
            'display_title' => $object->getOptions()->getDisplayTitle(),
            'banner_activate' => $object->getOptions()->isBannerActivate(),
            'display_post_view_counter' => $object->getOptions()->getDisplayPostViewCounter(),
            'banner_background_color' => $object->getOptions()->getBannerBackgroundColor(),
            'banner_height' => $object->getOptions()->getBannerHeight(),
            'banner_background_image' => $object->getOptions()->getBannerBackgroundImage(),
            'banner_background_image_position' => $object->getOptions()->getBannerBackgroundImagePosition(),
            'banner_background_image_repeat' => $object->getOptions()->getBannerBackgroundImageRepeat(),
            'tag_cloud' => (null === $object->getOptions()->getTagCloud()) ? 0 : $object->getOptions()->getTagCloud(),
        ];

        $data['posts'] = [];

        foreach ($object->getPosts() as $post) {
            $postUid = uniqid().'.txt';
            $postTemporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$postUid;
            file_put_contents($postTemporaryPath, $post->getContent());
            $files[$postUid] = $postTemporaryPath;

            $tags = [];

            foreach ($post->getTags() as $tag) {
                $tags[] = [
                    'name' => $tag->getName(),
                ];
            }

            $comments = [];

            foreach ($post->getComments() as $comment) {
                $commentUid = uniqid().'.txt';
                $commentTemporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$commentUid;
                file_put_contents($commentTemporaryPath, $comment->getMessage());
                $files[$commentUid] = $commentTemporaryPath;

                $comments[] = [
                    'message' => $commentUid,
                    'author' => $comment->getAuthor()->getMail(),
                    'creation_date' => $comment->getCreationDate()->format(\DateTime::ATOM),
                    'update_date' => (null !== $comment->getUpdateDate()) ? $comment->getUpdateDate()->format(\DateTime::ATOM) : null,
                    'publication_date' => (null !== $comment->getPublicationDate()) ? $comment->getPublicationDate()->format(\DateTime::ATOM) : null,
                    'status' => $comment->getStatus(),
                ];
            }

            $postArray = [
                'title' => $post->getTitle(),
                'content' => $postUid,
                'author' => $post->getAuthor()->getMail(),
                'status' => $post->getStatus(),
                'creation_date' => $post->getCreationDate()->format(\DateTime::ATOM),
                'modification_date' => (null !== $post->getModificationDate()) ? $post->getModificationDate()->format(\DateTime::ATOM) : null,
                'publication_date' => (null !== $post->getPublicationDate()) ? $post->getPublicationDate()->format(\DateTime::ATOM) : null,
                'tags' => $tags,
                'comments' => $comments,
            ];

            $data['posts'][] = $postArray;
        }

        return $data;
    }

    /**
     * @param array  $data
     * @param string $rootPath
     * @param User   $owner
     *
     * @return Blog
     */
    public function importBlog(array $data, $rootPath, User $owner)
    {
        $blogDatas = $data['data'];
        $optionsData = $blogDatas['options'];

        $blogOptions = new BlogOptions();
        $blogOptions
            ->setAuthorizeComment($optionsData['authorize_comment'])
            ->setAuthorizeAnonymousComment($optionsData['authorize_anonymous_comment'])
            ->setPostPerPage($optionsData['post_per_page'])
            ->setAutoPublishPost($optionsData['auto_publish_post'])
            ->setAutoPublishComment($optionsData['auto_publish_comment'])
            ->setDisplayTitle($optionsData['display_title'])
            ->setBannerActivate($optionsData['banner_activate'])
            ->setDisplayPostViewCounter($optionsData['display_post_view_counter'])
            ->setBannerBackgroundColor($optionsData['banner_background_color'])
            ->setBannerHeight($optionsData['banner_height'])
            ->setBannerBackgroundImage($optionsData['banner_background_image'])
            ->setBannerBackgroundImagePosition($optionsData['banner_background_image_position'])
            ->setBannerBackgroundImageRepeat($optionsData['banner_background_image_repeat'])
            ->setTagCloud($optionsData['tag_cloud']);

        $blog = new Blog();
        $blog->setOptions($blogOptions);

        $postsDatas = $blogDatas['posts'];
        $posts = new ArrayCollection();

        foreach ($postsDatas as $postsData) {
            $post = new Post();

            $tagsDatas = $postsData['tags'];
            $tags = new ArrayCollection();
            foreach ($tagsDatas as $tagsData) {
                $tag = $this->retrieveTag($tagsData['name']);
                $tags->add($tag);
            }

            $commentsDatas = $postsData['comments'];
            $comments = new ArrayCollection();
            foreach ($commentsDatas as $commentsData) {
                $comment = new Comment();
                $commentMessage = file_get_contents($rootPath.DIRECTORY_SEPARATOR.$commentsData['message']);
                $comment
                    ->setMessage($commentMessage)
                    ->setAuthor($this->retrieveUser($commentsData['author'], $owner))
                    ->setCreationDate(new \DateTime($commentsData['creation_date']))
                    ->setUpdateDate(new \DateTime($commentsData['update_date']))
                    ->setPublicationDate(new \DateTime($commentsData['publication_date']))
                    ->setStatus($commentsData['status'])
                ;
                $comments->add($comment);
            }

            $postContent = file_get_contents($rootPath.DIRECTORY_SEPARATOR.$postsData['content']);

            $post
                ->setTitle($postsData['title'])
                ->setContent($postContent)
                ->setAuthor($this->retrieveUser($postsData['author'], $owner))
                ->setCreationDate(new \DateTime($postsData['creation_date']))
                ->setModificationDate(new \DateTime($postsData['modification_date']))
                ->setPublicationDate(new \DateTime($postsData['publication_date']))
                ->setTags($tags)
                ->setComments($comments)
                ->setStatus($postsData['status'])
            ;

            $posts->add($post);
        }

        $blog->setPosts($posts);

        return $blog;
    }

    /**
     * @param string $mail
     * @param User   $owner
     *
     * @return User|null
     */
    protected function retrieveUser($mail, User $owner)
    {
        $user = $this->objectManager->getRepository('ClarolineCoreBundle:User')->findOneByMail($mail);

        if (null === $user) {
            $user = $owner;
        }

        return $user;
    }

    /**
     * @param string $name
     *
     * @return Tag
     */
    protected function retrieveTag($name)
    {
        $tag = $this->objectManager->getRepository('IcapBlogBundle:Tag')->findOneByName($name);

        if (null === $tag) {
            $tag = new Tag();
            $tag->setName($name);
        }

        return $tag;
    }

    /**
     * @param UploadedFile $file
     * @param BlogOptions  $options
     */
    public function updateBanner(UploadedFile $file = null, BlogOptions $options)
    {
        $ds = DIRECTORY_SEPARATOR;

        if (file_exists($this->uploadDir.$ds.$options->getBannerBackgroundImage()) || $file === null) {
            @unlink($this->uploadDir.$ds.$options->getBannerBackgroundImage());
        }

        if ($file) {
            $uniqid = uniqid();
            $options->setBannerBackgroundImage($uniqid);
            $file->move($this->uploadDir, $uniqid);
        } else {
            $options->setBannerBackgroundImage(null);
        }

        $this->objectManager->persist($options);
        $this->objectManager->flush();
    }

    public function getPanelInfos()
    {
        return array(
            'search',
            'infobar',
            'rss',
            'tagcloud',
            'redactor',
            'calendar',
            'archives',
        );
    }
}

<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 2/19/15
 */

namespace Icap\InwicastBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\EntityManager;
use Icap\InwicastBundle\Entity\Media;
use Icap\InwicastBundle\Entity\MediaCenter;
use Icap\InwicastBundle\Repository\MediaRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @DI\Service("inwicast.plugin.manager.media")
 */
class MediaManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Icap\InwicastBundle\Repository\MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaCenterUserManager
     */
    private $mediacenterUserManager;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @DI\InjectParams({
     *      "em"                        = @DI\Inject("doctrine.orm.entity_manager"),
     *      "mediaRepository"           = @DI\Inject("inwicast.plugin.repository.media"),
     *      "mediacenterUserManager"    = @DI\Inject("inwicast.plugin.manager.mediacenteruser"),
     *      "formFactory"               = @DI\Inject("form.factory")
     * })
     */
    public function __construct(
        EntityManager $em,
        MediaRepository $mediaRepository,
        MediaCenterUserManager $mediacenterUserManager,
        FormFactoryInterface $formFactory
    ) {
        $this->em = $em;
        $this->mediaRepository = $mediaRepository;
        $this->mediacenterUserManager = $mediacenterUserManager;
        $this->formFactory = $formFactory;
    }

    public function getByWidget($widgetInstance)
    {
        return $this->mediaRepository->findByWidget($widgetInstance);
    }

    public function getByWidgetOrEmpty($widgetInstance)
    {
        $media = $this->getByWidget($widgetInstance);
        if (empty($media)) {
            $media = $this->getEmptyMedia($widgetInstance);
        }

        return $media;
    }

    public function getEmptyMedia($widgetInstance)
    {
        $media = new Media();
        $media->setWidgetInstance($widgetInstance);

        return $media;
    }

    public function processPost($mediaRef, WidgetInstance $widget, MediaCenter $mediacenter, User $user)
    {
        $media = $this->getByWidgetOrEmpty($widget);
        if (!empty($mediaRef) && $mediaRef !== $media->getMediaRef()) {
            $media->setMediaRef($mediaRef);
            $media = $this->getMediaInfo($media, $mediacenter, $user);

            $this->em->persist($media);
            $this->em->flush();
        }

        return $media;
    }

    public function getMediaInfo(Media $media, MediaCenter $mediacenter, User $user)
    {
        $token = $this->mediacenterUserManager->getMediacenterUserToken($user, $mediacenter);
        $mediacenterUrl = $mediacenter->getUrl();
        $parameters = 'op=get_media_info_json&userName='.$user->getUsername().'&token='.$token;
        $parameters = $parameters.'&mediaRef='.$media->getMediaRef();
        $data = file_get_contents($mediacenterUrl.'api/?'.$parameters);

        $data = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data);
        $video = json_decode($data);
        if ($video !== null) {
            $media
                ->setTitle($video->title)
                ->setDescription($video->description)
                ->setDate($video->mediaDate)
                ->setPreviewUrl($video->previewUrl)
                ->setViews($video->viewed)
                ->setWidth($video->width)
                ->setHeight($video->height);
        }

        return $media;
    }

    public function getMediaListForUser(User $user, MediaCenter $mediacenter, $keywords = null)
    {
        $token = $this->mediacenterUserManager->getMediacenterUserToken($user, $mediacenter);
        $medialist = [];
        $mediacenterUrl = $mediacenter->getUrl();
        $parameters = 'op=get_user_medias_json&userName='.$user->getUsername().'&token='.$token;
        if ($keywords !== null) {
            $parameters = $parameters.'&keywords='.$keywords;
        }
        $data = file_get_contents($mediacenterUrl.'api/?'.$parameters);
        $data = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data);
        $jsonresult = json_decode($data);
        if ($jsonresult !== null) {
            $videos = $jsonresult->videos;
            if ($videos !== null) {
                foreach ($videos as $video) {
                    $medialist[] = new Media(
                        $video->mediaRef,
                        $video->title,
                        $video->description,
                        $video->mediaDate,
                        $video->previewUrl,
                        $video->viewed,
                        $video->width,
                        $video->height
                    );
                }
            }
        }

        return $medialist;
    }

    public function getMediaUrl($mediaRef, MediaCenter $mediacenter, User $user = null)
    {
        $token = $this->mediacenterUserManager->getMediacenterUserToken($user, $mediacenter);
        $mediacenterUrl = $mediacenter->getUrl();
        $parameters = 'video='.$mediaRef.'&userName='.$user->getUsername().'&token='.$token;
        $mediaUrl = $mediacenterUrl.'videos/?'.$parameters;

        return $mediaUrl;
    }
}

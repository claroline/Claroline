<?php

namespace Claroline\VideoPlayerBundle\Tests\API;

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    public function testGetTracksAction()
    {
        $manager = $this->createManager();
        //we log before because we need the securty context for the resource creation
        $this->login($manager);
        $file = $this->persister->file('video', 'video/mp4', true, $manager);
        $this->createTrack($file, 'en', 'English', false);
        $this->createTrack($file, 'fr', 'Français', false);
        $this->createTrack($file, 'es', 'español, castellano', false);
        $this->client->request('GET', "/video-player/api/video/{$file->getId()}/tracks");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(3, count($data));
    }

    /**
     * @Get("/video/track/{track}/stream", name="get_video_track_stream", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_video"})
     */
    public function testStreamTrackAction()
    {
        $manager = $this->createManager();
        //we log before because we need the securty context for the resource creation
        $this->login($manager);
        $file = $this->persister->file('video', 'video/mp4', true, $manager);
        $track = $this->createTrack($file, 'en', 'English', false);
        $this->client->request('GET', "/video-player/api/video/track/{$track->getId()}/stream");
        //this is a bad check but it's better than nothing
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @Get("/video/{video}/stream", name="get_video_stream", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_video"})
     */
    public function testStreamVideoAction()
    {
        $this->markTestSkipped('We must simulate a file upload to do this');
        $manager = $this->createManager();
        //we log before because we need the securty context for the resource creation
        $this->login($manager);
        $file = $this->persister->file('video', 'video/mp4', true, $manager);
        $this->client->request('GET', "/video-player/api/video/{$file->getId()}/stream");
        //this is a bad check but it's better than nothing
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    private function createTrack(File $video, $lang, $label = 'English', $isDefault = false)
    {
        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'tmp'),
            'subtitles.vtt',
            null,
            null,
            null,
            true //test mode
        );
        $trackData = [
            'id' => Uuid::uuid4()->toString(),
            'video' => [
                'id' => $video->getId(),
            ],
            'meta' => [
                'label' => $label,
                'lang' => $lang,
                'kind' => 'subtitles',
                'default' => $isDefault,
            ],
            'file' => $file,
        ];

        return $this->client->getContainer()->get('claroline.api.crud')->create(
            'Claroline\VideoPlayerBundle\Entity\Track',
            $trackData
        );
    }

    private function createManager()
    {
        $manager = $this->persister->user('manager');
        $role = $this->persister->role('ROLE_ADMIN');
        $manager->addRole($role);
        $this->persister->persist($manager);

        return $manager;
    }
}

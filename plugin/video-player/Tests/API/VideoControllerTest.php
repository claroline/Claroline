<?php

namespace Claroline\VideoPlayerBundle\Tests\API;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Entity\Resource\File;

class VideoControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    public function testPostTrackAction()
    {
        $manager = $this->createManager();
        $file = $this->persister->file('video', 'video/mp4', true, $manager);
        $this->persister->flush();
        //we log before because we need the securty context for the resource creation
        $this->login($manager);
        $subtitles = new UploadedFile(tempnam(sys_get_temp_dir(), 'tmp'), 'subtitles.vtt');
        $form = ['track' => ['default' => true, 'lang' => 'en', 'label' => 'English']];
        $files = ['track' => ['track' => $subtitles]];
        $this->client->request('POST', "/video-player/api/video/{$file->getId()}/track", $form, $files);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('subtitles', $data['kind']);
    }

    public function testGetTracksAction()
    {
        $manager = $this->createManager();
        //we log before because we need the securty context for the resource creation
        $this->login($manager);
        $file = $this->persister->file('video', 'video/mp4', true, $manager);
        $this->createTrack($file, 'en');
        $this->createTrack($file, 'fr');
        $this->createTrack($file, 'es');
        $this->client->request('GET', "/video-player/api/video/{$file->getId()}/tracks");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(3, count($data));
    }

    public function testDeleteTrackAction()
    {
        $manager = $this->createManager();
        //we log before because we need the securty context for the resource creation
        $this->login($manager);
        $file = $this->persister->file('video', 'video/mp4', true, $manager);
        $toRemove = $this->createTrack($file, 'en');
        $this->createTrack($file, 'fr');
        $this->createTrack($file, 'es');
        $this->client->request('DELETE', "/video-player/api/video/track/{$toRemove->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', "/video-player/api/video/{$file->getId()}/tracks");
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(2, count($data));
    }

    public function testPutTrackAction()
    {
        $manager = $this->createManager();
        //we log before because we need the securty context for the resource creation
        $this->login($manager);
        $file = $this->persister->file('video', 'video/mp4', true, $manager);
        $toEdit = $this->createTrack($file, 'en');
        $form = ['track' => ['default' => true, 'lang' => 'fr', 'label' => 'Français']];
        $this->client->request('PUT', "/video-player/api/video/track/{$toEdit->getId()}", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals('fr', $data['lang']);
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
        $track = $this->createTrack($file, 'en');
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

    private function createTrack(File $video, $lang)
    {
        $trackData = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'tmp'),
            'subtitles.vtt',
            null,
            null,
            null,
            true //test mode
        );

        return $this->client->getContainer()->get('claroline.manager.video_player_manager')->createTrack(
            $video,
            $trackData,
            $lang,
            'langlabel'
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

# File players

File is a basic resource type defined by the platform. Theses resources are differents from
the others as their expected behavior is different depending on their mime type.

The Claroline platform consider each mime this way: baseMime/extension.

eg: video/mp4 where video is the base and mp4 is the extension.

Each time a user is trying to open a file, the platform will fire some events.
First it'll dispatch the most specific one:

    play_file_basetype_extension

If no response was given to the the event, it'll try the more generic one:

    play_file_basetype

Finally, it'll ask for the resource download.

## Players implementation

In order to catch the event, your plugin must define a listener in your config.

This example will show you the main files of a basic HTML5 video player.

**The listener config file**

*Claroline\VideoPlayer\Resources\config\services\listener.yml*

    services:
        claroline.listener.video_player_listener:
            class: Claroline\VideoPlayerBundle\Listener\VideoPlayerListener
            calls:
                - [setContainer, ["@service_container"]]
            tags:
                - { name: kernel.event_listener, event: play_file_video, method: onOpenVideo }

**The listener class**

*Claroline\VideoPlayerBundle\Listener\VideoPlayerListener.php*

    namespace Claroline\VideoPlayerBundle\Listener;

    use Claroline\CoreBundle\Library\Event\PlayFileEvent;
    use Symfony\Component\DependencyInjection\ContainerAware;
    use Symfony\Component\HttpFoundation\Response;

    class VideoPlayerListener extends ContainerAware
    {
        public function onOpenVideo(PlayFileEvent $event)
        {
            $path = $this->container->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR.$event->getInstance()->getResource()->getHashName();
            $content = $this->container->get('templating')
                ->render('ClarolineVideoPlayerBundle::video.html.twig',
                    array('workspace' => $event->getInstance()->getWorkspace(), 'path' => $path, 'video' => $event->getInstance()->getResource()));
            $response = new Response($content);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

**The template twig file**

*Claroline\VideoPlayerBundle\Resources\view\video.html.twig*

    {% extends "ClarolineCoreBundle:Workspace:layout.html.twig" %}

    {% block section_content %}
    <video controls preload=none
        <source src="{{ path ('claro_stream_video', {'videoId': video.getId()})}}"/>
    </video>
    {% endblock %}

**The controller**

*Claroline\VideoPlayerBundle\Controller\VideoPlayerController.php*

    namespace Claroline\VideoPlayerBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\StreamedResponse;

    class VideoPlayerController extends Controller
    {
        public function streamAction($videoId)
        {
            $video = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Resource\File')->find($videoId);

            $response = new StreamedResponse();
            $path = $this->container->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR.$video->getHashName();
            $response->setCallBack(function() use($path){
                readfile($path);
            });
            $response->headers->set('Content-Type', $video->getMimeType());

            return $response;
        }
    }

**The routing file**

*Claroline\VideoPlayerBundle\Resources\config\routing.yml*

    claro_stream_video:
    pattern: /stream/video/{videoId}
    defaults: { _controller: ClarolineVideoPlayerBundle:VideoPlayer:stream }

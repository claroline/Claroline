[[Documentation index]][1]

File players
============

File is a basic resource type defined by the platform. Theses resources are
differents from the others as their expected behavior is different depending on
their mime type.

The Claroline platform consider each mime this way: baseMime/extension.

eg: video/mp4 where video is the base and mp4 is the extension.

Each time a user is trying to open a file, the platform will fire some events.
First it'll dispatch the most specific one:

```
play_file_basetype_extension
```

If no response was given to the the event, it'll try the more generic one:

```
play_file_basetype
```

Finally, it'll ask for the resource download.

Players implementation
----------------------

In order to catch the event, your plugin must define a listener in your config.

This example will show you the main files of a basic HTML5 video player.

### The listener class ###

*Claroline\VideoPlayerBundle\Listener\VideoPlayerListener.php*

```php
<?php

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Event\PlayFileEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;

/**
* @DI\Service("claroline.listener.video_player_listener")
*/
class VideoPlayerListener extends ContainerAware
{
    private $fileDir;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "fileDir" = @DI\Inject("%claroline.param.files_directory%"),
     *     "templating" = @DI\Inject("templating")
     * })
     */
    public function __construct($fileDir, $templating)
    {
        $this->fileDir = $fileDir;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("play_file_video")
     */
    public function onOpenVideo(PlayFileEvent $event)
    {
        $path = $this->fileDir . DIRECTORY_SEPARATOR . $event->getResource()->getHashName();
        $content = $this->templating->render(
            'ClarolineVideoPlayerBundle::video.html.twig',
            array(
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $path,
                'video' => $event->getResource(),
                '_resource' => $event->getResource()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
```

**Important:** The '_resource' parameter is required by the core to render the
resource breadcrumbs.

### The template twig file ###

*Claroline\VideoPlayerBundle\Resources\view\video.html.twig*

```html+jinja
{% set layout = "ClarolineCoreBundle:Workspace:layout.html.twig" %}

{% if isDesktop() %}
    {% set layout = "ClarolineCoreBundle:Desktop:layout.html.twig" %}
{% endif %}

{% extends layout %}

{% block section_content %}
    <div class="panel-heading">
        <h3 class="panel-title">{{ video.getResourceNode().getName() }}</h3>
    </div>
    <div class="panel-body">
        <video width="100%" controls>
            <source src="{{ path ('claro_stream_video', {'node': video.getResourceNode().getId()}) }}" type="{{ video.getMimeType() }}">
            <!-- In case of the browser does not support the video tag: -->
            <object width="100%" height="400">
            <param name="movie" value="{{ path ('claro_stream_video', {'node': video.getResourceNode().getId()})}}">
                <embed src="{{ path ('claro_stream_video', {'node': video.getResourceNode().getId()})}}"></embed>
            </object>
        </video>
    </div>
    <div class="panel-footer">
        <a class="btn btn-primary" href="{{ path('claro_resource_download') }}?ids[]={{video.getResourceNode().getResourceNode().getId()}}">
            <i class="fa fa-arrow-circle-o-down"></i> {{ 'download'|trans({}, 'platform') }}
        </a>
    </div>
{% endblock %}
```

**Important:** Notice the {% extends layout %} and the above code. It will set
the correct layout of your page.

### The controller ###

*Claroline\VideoPlayerBundle\Controller\VideoPlayerController.php*

```php
namespace Claroline\VideoPlayerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

//todo use sf2.2 BinaryFileResponse
class VideoPlayerController extends Controller
{
    /**
     * @Route(
     *     "/stream/video/{node}",
     *     name="claro_stream_video"
     * )
     */
    public function streamAction(ResourceNode $node)
    {
        $video = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);
        $response = new StreamedResponse();
        $path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $video->getHashName();
        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );
        $response->headers->set('Content-Type', $node->getMimeType());

        return $response;
    }
}
```

### The routing file ###

*Claroline\VideoPlayerBundle\Resources\config\routing.yml*

```yml
claro_stream_video:
pattern: /stream/video/{videoId}
defaults: { _controller: ClarolineVideoPlayerBundle:VideoPlayer:stream }
```

[[Documentation index]][1]

[1]: ../../index.md

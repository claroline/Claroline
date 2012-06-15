<?php
namespace Claroline\VideoPlayerBundle\Player;

use Claroline\CoreBundle\Library\Resource\PlayerInterface;
use Symfony\Component\HttpFoundation\Response;

class VideoPlayer //implements PlayerInterface
{
    public function indexAction($workspaceId, $resourceId)
    {
        return new Response("redéfini openAction pour mp4, l'id de mon workspace est {$workspaceId}");
    }

    public function getMimeType()
    {
        return "video/mp4";
    }

    public function getPlayerName()
    {
        return "mp4Player";
    }
}
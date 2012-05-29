<?php
namespace Claroline\VideoPlayerBundle\Player;

use Claroline\CoreBundle\Library\Player\PlayerInterface;
use Symfony\Component\HttpFoundation\Response;

class VideoPlayer implements PlayerInterface
{
    public function getExtension()
    {
        return "mp4";
    }
    
    public function indexAction()
    {
        return new Response ("redéfini openAction pour mp4");
    }
}
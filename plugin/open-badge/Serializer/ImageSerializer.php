<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\CoreBundle\Entity\File\PublicFile;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Service("claroline.serializer.open_badge.image")
 */
class ImageSerializer
{
    /**
     * @DI\InjectParams({
     *     "router" = @DI\Inject("router")
     * })
     *
     * @param Router $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function serialize(PublicFile $file)
    {
        $context = $this->router->getContext();
        $schemeAndHttpHost = $context->getScheme().'://'.$context->getHost().'/';

        return  [
            'type' => 'Image',
            'id' => $schemeAndHttpHost.$file->getUrl(),
            //no captions atm
            'caption' => '',
            'author' => $this->router->generate('apiv2_open_badge__profile', ['profile' => $file->getCreator()->getUuid()]),
        ];
    }
}

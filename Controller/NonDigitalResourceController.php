<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Innova\PathBundle\Entity\NonDigitalResource;

class NonDigitalResourceController extends ContainerAware
{
    /**
     *
     * @Route(
     *     "/non_digital_resource/view/{nonDigitalResourceId}",
     *     name = "innova_nondigitalresource_player"
     * )
     * @ParamConverter("nonDigitalResource", class="InnovaPathBundle:NonDigitalResource", options={"id" = "nonDigitalResourceId"})
     * @Template("InnovaPathBundle:NonDigitalResource:view.html.twig")
     */
    public function viewAction(NonDigitalResource $nonDigitalResource)
    {
        return array (
            'nonDigitalResource' => $nonDigitalResource,
        );
    }
}
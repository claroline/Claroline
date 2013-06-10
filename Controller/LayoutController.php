<?php

namespace ICAP\BlogBundle\Controller;

use Claroline\CoreBundle\Controller\LayoutController as ClarolineLayoutController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class LayoutController extends ClarolineLayoutController
{
    /**
     * @param integer|null $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topBarAction($workspaceId = null)
    {
        $user              = $this->get('security.context')->getToken()->getUser();
        $username          = $user->getFirstName() . ' ' . $user->getLastName();
        $personalWorkspace = $user->getPersonalWorkspace();

        return $this->render(
            'ICAPBlogBundle:Layout:top_bar.html.twig',
            array(
                'username'          => $username,
                'personalWorkspace' => $personalWorkspace,
                "isImpersonated"    => $this->isImpersonated(),
            )
        );
    }
}

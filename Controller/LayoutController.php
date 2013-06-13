<?php

namespace ICAP\BlogBundle\Controller;

use Claroline\CoreBundle\Controller\LayoutController as ClarolineLayoutController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class LayoutController extends ClarolineLayoutController
{
    /**
     * @param integer $blogId
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topBarAction($blogId = null)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $blog          = $entityManager->getRepository('ICAPBlogBundle:Blog')->findOneById($blogId);

        if (!$blog) {
            throw $this->createNotFoundException(sprintf("No blog found for id '%s'.", $blogId));
        }

        $user              = $this->get('security.context')->getToken()->getUser();
        $username          = $user->getFirstName() . ' ' . $user->getLastName();

        return $this->render(
            'ICAPBlogBundle:Layout:top_bar.html.twig',
            array(
                'blog'              => $blog,
                'username'          => $username,
                'personalWorkspace' => $user->getPersonalWorkspace(),
                'isImpersonated'    => $this->isImpersonated(),
            )
        );
    }
}

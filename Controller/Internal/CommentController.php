<?php

namespace Icap\PortfolioBundle\Controller\Internal;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Controller\Controller as BaseController;
use Icap\PortfolioBundle\Entity\Portfolio;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/internal/portfolio/{id}")
 */
class CommentController extends BaseController
{
    /**
     * @Route("/comment", name="icap_portfolio_internal_comment_post")
     * @Method({"POST"})
     *
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     */
    public function postAction(Request $request, User $loggedUser, Portfolio $portfolio)
    {
        $this->checkPortfolioToolAccess();

        $commentManager = $this->getCommentsManager();

        $newComment = $commentManager->getNewComment($portfolio, $loggedUser);
        $data       = $commentManager->handle($newComment, $loggedUser, $request->request->all());

        $response = new JsonResponse($data, Response::HTTP_OK);

        return $response;
    }
}
 
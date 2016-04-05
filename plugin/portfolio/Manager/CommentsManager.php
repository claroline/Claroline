<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioComment;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Icap\PortfolioBundle\Factory\CommentFactory;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service("icap_portfolio.manager.comments")
 */
class CommentsManager
{
    /** @var EntityManager  */
    protected $entityManager;

    /** @var FormFactory  */
    protected $formFactory;

    /** @var CommentFactory  */
    protected $commentFactory;

    /** @var PortfolioGuideManager  */
    protected $portfolioGuideManager;

    /**
     * @DI\InjectParams({
     *     "entityManager"         = @DI\Inject("doctrine.orm.entity_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "commentFactory"        = @DI\Inject("icap_portfolio.factory.comment"),
     *     "portfolioGuideManager" = @DI\Inject("icap_portfolio.manager.portfolio_guide")
     * })
     */
    public function __construct(EntityManager $entityManager, FormFactory $formFactory, CommentFactory $commentFactory, PortfolioGuideManager $portfolioGuideManager)
    {
        $this->entityManager         = $entityManager;
        $this->formFactory           = $formFactory;
        $this->commentFactory        = $commentFactory;
        $this->portfolioGuideManager = $portfolioGuideManager;
    }

    /**
     * @param PortfolioComment $comment
     * @param User             $user
     * @param array            $parameters
     * @param string           $env
     *
     * @return array
     */
    public function handle(PortfolioComment $comment, User $user, array $parameters, $env = 'prod')
    {
        $form = $this->formFactory->create('icap_portfolio_portfolio_comment_form', $comment);
        $form->submit($parameters);

        if ($form->isValid()) {
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $portfolio = $comment->getPortfolio();
            if ($portfolio->getUser() === $user) {
                $portfolio->setCommentsViewAt($comment->getSendingDate());

                $this->entityManager->persist($comment);
                $this->entityManager->flush();
            }
            else {
                $portfolioGuide = $this->portfolioGuideManager->getByPortfolioAndGuide($portfolio, $user);

                if (null !== $portfolioGuide) {
                    $this->portfolioGuideManager->updateCommentsViewDate($portfolioGuide);
                }
            }

            return $comment->getData();
        }

        if ('dev' === $env) {
            echo "<pre>";
            foreach ($form->getErrors(true, false) as $formError) {
                var_dump($formError->getMessage());
                var_dump($formError->getMessageParameters());
            }
            echo "</pre>" . PHP_EOL;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param Portfolio $portfolio
     * @param User      $user
     *
     * @return PortfolioComment
     */
    public function getNewComment(Portfolio $portfolio, User $user)
    {
        $comment = $this->commentFactory->createComment($portfolio, $user);

        return $comment;
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return array
     */
    public function getCommentsByPortfolio(Portfolio $portfolio)
    {
        $comments = array();

        /** @var \Icap\PortfolioBundle\Entity\PortfolioComment[] $commentObjects */
        $commentObjects = $this->entityManager->getRepository('IcapPortfolioBundle:PortfolioComment')->findByPortfolio($portfolio);

        foreach ($commentObjects as $commentObject) {
            $comments[] = $commentObject->getData();
        }

        return $comments;
    }
}
 
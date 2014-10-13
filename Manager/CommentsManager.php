<?php

namespace Icap\PortfolioBundle\Manager;

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

    /**
     * @DI\InjectParams({
     *     "entityManager"  = @DI\Inject("doctrine.orm.entity_manager"),
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "commentFactory" = @DI\Inject("icap_portfolio.factory.comment")
     * })
     */
    public function __construct(EntityManager $entityManager, FormFactory $formFactory, CommentFactory $commentFactory)
    {
        $this->entityManager  = $entityManager;
        $this->formFactory    = $formFactory;
        $this->commentFactory = $commentFactory;
    }

    /**
     * @param PortfolioComment $comment
     * @param array            $parameters
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function handle(PortfolioComment $comment, array $parameters)
    {
        $form = $this->formFactory->create('icap_portfolio_portfolio_comment_form', $comment);
        $form->submit($parameters);

        if ($form->isValid()) {
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $comment->getData();
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param Portfolio $portfolio
     *
     * @throws \InvalidArgumentException
     * @return PortfolioComment
     */
    public function getNewComment(Portfolio $portfolio)
    {
        $comment = $this->commentFactory->createComment($portfolio);

        return $comment;
    }
}
 
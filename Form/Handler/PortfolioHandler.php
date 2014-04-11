<?php

namespace Icap\PortfolioBundle\Form\Handler;

use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Manager\PortfolioManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_portfolio.form_handler.portfolio")
 */
class PortfolioHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var PortfolioManagerManager
     */
    protected $portfolioManager;

    /**
     * @DI\InjectParams({
     *     "portfolioForm"    = @DI\Inject("icap_portfolio.form.portfolio"),
     *     "request"          = @DI\Inject("request_stack"),
     *     "entityManager"    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "portfolioManager" = @DI\Inject("icap_portfolio.manager.portfolio")
     * })
     */
    public function __construct(FormInterface $portfolioForm, RequestStack $requestStack, EntityManager $entityManager, PortfolioManager $portfolioManager)
    {
        $this->form             = $portfolioForm;
        $this->requestStack     = $requestStack;
        $this->entityManager    = $entityManager;
        $this->portfolioManager = $portfolioManager;
    }

    /**
     * @param  Portfolio $portfolio
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleAdd(Portfolio $portfolio)
    {
        $this->form->setData($portfolio);

        $request = $this->requestStack->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $this->form->submit($request);

            if ($this->form->isValid()) {
                $this->entityManager->persist($portfolio);
                $this->entityManager->flush();

                return true;
            }
        }

        return false;
    }

    /**
     * @param  Badge $badge
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleEdit(Badge $badge)
    {
        $this->form->setData($badge);

        /** @var BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $originalRules */
        $originalRules = $badge->getRules();

        if ($this->request->isMethod('POST')) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $badgeRules = $badge->getRules();

                $userBadges = $badge->getUserBadges();

                if (0 < count($userBadges) && $this->badgeManager->isRuleChanged($badgeRules, $originalRules)) {
                    /** @var \Doctrine\ORM\UnitOfWork $unitOfWork */
                    $unitOfWork = $this->entityManager->getUnitOfWork();

                    $newBadge = clone $badge;
                    $newBadge->setVersion($badge->getVersion() + 1);

                    $unitOfWork->refresh($badge);

                    $badge->setDeletedAt(new \DateTime());

                    $this->entityManager->persist($newBadge);
                }
                else {
                    // Compute which rules was deleted
                    foreach ($badgeRules as $rule) {
                        if ($originalRules->contains($rule)) {
                            $originalRules->removeElement($rule);
                        }
                    }

                    // Delete rules
                    foreach ($originalRules as $rule) {
                        $this->entityManager->remove($rule);
                    }
                }

                $this->entityManager->persist($badge);
                $this->entityManager->flush();

                return true;
            }
        }

        return false;
    }
}
 
<?php

namespace Claroline\CoreBundle\Form\Badge\Handler;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Manager\BadgeManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Service("claroline.form_handler.badge", scope="request")
 */
class BadgeHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var BadgeManager
     */
    protected $badgeManager;

    /**
     * @DI\InjectParams({
     *     "form"          = @DI\Inject("claroline.form.badge"),
     *     "request"       = @DI\Inject("request"),
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "badgeManager"  = @DI\Inject("claroline.manager.badge")
     * })
     */
    public function __construct(FormInterface $form, Request $request, EntityManager $entityManager, BadgeManager $badgeManager)
    {
        $this->form          = $form;
        $this->request       = $request;
        $this->entityManager = $entityManager;
        $this->badgeManager  = $badgeManager;
    }

    /**
     * Process form
     *
     * @param  Badge  $badge
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handle(Badge $badge)
    {
        $this->form->setData($badge);

        /** @var BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $originalRules */
        $originalRules = $badge->getRules();

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $badgeRules = $badge->getRules();

                if ($this->badgeManager->isRuleChanged($badgeRules, $originalRules)) {
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
 
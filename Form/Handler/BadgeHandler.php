<?php

namespace Icap\BadgeBundle\Form\Handler;

use Icap\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Manager\BadgeManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

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

    public function __construct(FormInterface $form, Request $request, EntityManager $entityManager, BadgeManager $badgeManager)
    {
        $this->form          = $form;
        $this->request       = $request;
        $this->entityManager = $entityManager;
        $this->badgeManager  = $badgeManager;
    }

    /**
     * @param  Badge $badge
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleAdd(Badge $badge)
    {
        $this->form->setData($badge);

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->entityManager->persist($badge);
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
 
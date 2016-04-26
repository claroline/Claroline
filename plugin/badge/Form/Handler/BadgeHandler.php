<?php

namespace Icap\BadgeBundle\Form\Handler;

use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Manager\BadgeManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    protected $uploadDir;

    public function __construct(FormInterface $form, Request $request, EntityManager $entityManager, BadgeManager $badgeManager, $webDir)
    {
        $this->form = $form;
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->badgeManager = $badgeManager;
        $this->uploadDir = $webDir.DIRECTORY_SEPARATOR.Badge::getUploadDir();
    }

    /**
     * @param Badge $badge
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function handleAdd(Badge $badge)
    {
        $this->form->setData($badge);

        if ($this->request->isMethod('POST')) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->handleUpload($this->form->get('file')->getData(), $badge);
                $this->entityManager->persist($badge);
                $this->entityManager->flush();

                return true;
            }
        }

        return false;
    }

    /**
     * @param Badge $badge
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
                $this->handleUpload($this->form->get('file')->getData(), $badge);
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
                } else {
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

    public function handleDelete(Badge $badge)
    {
        $ds = DIRECTORY_SEPARATOR;
        $imagePath = $badge->getImagePath();

        $this->entityManager->remove($badge);
        $this->entityManager->flush();

        if ($imagePath !== null && file_exists($this->uploadDir.$ds.$imagePath)) {
            @unlink($this->uploadDir.$ds.$imagePath);
        }
    }

    private function handleUpload(UploadedFile $file = null, Badge $badge)
    {
        $ds = DIRECTORY_SEPARATOR;

        if ($file !== null) {
            if (file_exists($this->uploadDir.$ds.$badge->getImagePath())) {
                @unlink($this->uploadDir.$ds.$badge->getImagePath());
            }
            $filename = sha1(uniqid(mt_rand(), true)).'.'.$file->guessExtension();
            $badge->setImagePath($filename);
            $realpathUploadRootDir = realpath($this->uploadDir);

            if (false === $realpathUploadRootDir) {
                throw new \Exception(
                    sprintf(
                        "Invalid upload root dir '%s'for uploading badge images.",
                        $this->uploadDir
                    )
                );
            }
            $file->move($this->uploadDir, $filename);
        }
    }
}

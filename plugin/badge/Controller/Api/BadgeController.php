<?php

namespace Icap\BadgeBundle\Controller\Api;

use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Repository\BadgeRepository;
use FOS\RestBundle\View\ViewHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Route("/api/badges", service="icap_badge.api.badge")
 * @Service("icap_badge.api.badge")
 */
class BadgeController
{
    /**
     * @var BadgeRepository
     */
    private $badgeRepository;

    /**
     * @var ViewHandler
     */
    private $viewHandler;

    /**
     * @InjectParams({
     *     "badgeRepository" = @Inject("icap_badge.repository.badge"),
     *     "viewHandler"     = @Inject("fos_rest.view_handler")
     * })
     */
    public function __construct(BadgeRepository $badgeRepository, ViewHandler $viewHandler)
    {
        $this->badgeRepository = $badgeRepository;
        $this->viewHandler = $viewHandler;
    }

    /**
     * @Route("/", name="icap_badge_api_badge_all", defaults={"_format" = "json"})
     */
    public function allAction()
    {
        $badges = $this->badgeRepository->findAll();

        $view = View::create()
            ->setStatusCode(200)
            ->setData($badges);

        return $this->viewHandler->handle($view);
    }

    /**
     * @Route("/{id}", name="icap_badge_api_badge_get", requirements={"id" = "\d+"}, defaults={"_format" = "json"})
     */
    public function getAction($id)
    {
        /** @var \Icap\BadgeBundle\Entity\Badge $badge */
        $badge = $this->badgeRepository->find($id);

        if (null === $badge) {
            throw new NotFoundHttpException('Badge not found');
        }

        $view = View::create()
            ->setStatusCode(200)
            ->setData($badge);

        return $this->viewHandler->handle($view);
    }
}

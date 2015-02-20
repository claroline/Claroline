<?php

namespace Icap\BadgeBundle\Controller\Api;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Repository\Badge\BadgeRepository;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Route("/api/badges", service="claroline.api.badge")
 * @Service("claroline.api.badge")
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
     *     "badgeRepository" = @Inject("claroline.repository.badge"),
     *     "viewHandler"     = @Inject("fos_rest.view_handler")
     * })
     */
    public function __construct(BadgeRepository $badgeRepository, ViewHandler $viewHandler)
    {
        $this->badgeRepository = $badgeRepository;
        $this->viewHandler     = $viewHandler;
    }

    /**
     * @Route("/", name="claro_api_badge_all", defaults={"_format" = "json"})
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
     * @Route("/{id}", name="claro_api_badge_get", requirements={"id" = "\d+"}, defaults={"_format" = "json"})
     */
    public function getAction($id)
    {
        /** @var \Claroline\CoreBundle\Entity\Badge\Badge $badge */
        $badge = $this->badgeRepository->find($id);

        if (null === $badge) {
            throw new NotFoundHttpException("Badge not found");
        }

        $view = View::create()
            ->setStatusCode(200)
            ->setData($badge);

        return $this->viewHandler->handle($view);
    }
}

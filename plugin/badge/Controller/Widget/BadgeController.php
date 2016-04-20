<?php

namespace Icap\BadgeBundle\Controller\Widget;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BadgeController extends Controller
{
    /**
     * @EXT\Route("/widget/badge/config/{widgetInstance}", name="icap_badge_badge_usage_update_config")
     * @EXT\Method("POST")
     */
    public function updateAction(WidgetInstance $widgetInstance, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        /** @var \Icap\BadgeBundle\Manager\BadgeWidgetManager $badgeWidgetManager */
        $badgeWidgetManager = $this->get('icap_badge.manager.badge_widget');
        $badgeUsageWidgetConfig = $badgeWidgetManager->getBadgeUsageConfigForInstance($widgetInstance);

        $form = $this->get('icap_badge.widget.form.badge_usage');
        $form
            ->setData($badgeUsageWidgetConfig)
            ->handleRequest($request);

        if ($form->isValid()) {
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->persist($badgeUsageWidgetConfig);
            $entityManager->flush();
        }

        return new Response('success', 204);
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Widget;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BadgeController extends Controller
{
    /**
     * @EXT\Route("/widget/badge/config/{widgetInstance}", name="claro_badge_usage_update_config")
     * @EXT\Method("POST")
     */
    public function updateAction(WidgetInstance $widgetInstance, Request $request)
    {
        if (!$this->get('security.context')->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        /** @var \Claroline\CoreBundle\Manager\BadgeWidgetManager $badgeWidgetManager */
        $badgeWidgetManager = $this->get("claroline.manager.badge_widget");
        $badgeUsageWidgetConfig = $badgeWidgetManager->getBadgeUsageConfigForInstance($widgetInstance);

        $form = $this->get('claroline.widget.form.badge_usage');
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

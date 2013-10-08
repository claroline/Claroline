<?php

namespace Claroline\CoreBundle\Controller\Badge;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BadgeController extends Controller
{
    public function listAction($parameters)
    {
        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository $badgeRepository */
        $badgeRepository = $this->get('claroline.repository.badge');

        /** @var QueryBuilder $badgeQueryBuilder */
        $badgeQueryBuilder = $badgeRepository->findOrderedByName($platformConfigHandler->getParameter('locale_language'), false);

        if (isset($parameters['workspace']) && null !== $parameters['workspace']) {
            $badgeQueryBuilder
                ->andWhere('badge.workspace = :workspace')
                ->setParameter('workspace', $parameters['workspace']);

            $badgeClaimsWorkspace = $parameters['workspace'];
        }
        else {
            $badgeClaimsWorkspace = null;
        }

        $badgeClaims = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\BadgeClaim')->findByWorkspace($badgeClaimsWorkspace);

        $language = $platformConfigHandler->getParameter('locale_language');

        /** @var \Claroline\CoreBundle\Pager\PagerFactory $pagerFactory */
        $pagerFactory = $this->get('claroline.pager.pager_factory');

        $pager = $pagerFactory->createPager($badgeQueryBuilder->getQuery(), $parameters['page'], 10);

        return $this->render(
            'ClarolineCoreBundle:Badge:Template/list.html.twig',
            array(
                'pager'       => $pager,
                'language'    => $language,
                'parameters'  => $parameters,
                'badgeClaims' => $badgeClaims
            )
        );
    }
}
